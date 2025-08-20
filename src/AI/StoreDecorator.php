<?php

namespace App\AI;

use App\Repository\UserRepository;
use Codewithkyrian\ChromaDB\Client;
use Symfony\AI\Platform\Vector\Vector;
use Symfony\AI\Store\Bridge\ChromaDb\Store;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\VectorDocument;
use Symfony\AI\Store\VectorStoreInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Uid\Uuid;

#[AsDecorator(decorates: Store::class)]
final readonly class StoreDecorator implements VectorStoreInterface
{
    public function __construct(
        private RequestStack $requestStack,
        private UserRepository $userRepository,
        private Client $client,
        private string $collectionName,

        #[AutowireDecorated]
        private Store $inner,
    ) {
    }

    public function add(VectorDocument ...$documents): void
    {
        $this->inner->add(...$documents);
    }

    public function query(Vector $vector, array $options = []): array
    {
        /** @var ?int $userId */
        $userId = $this->requestStack->getSession()->get('userId');
        $user = null !== $userId
            ? $this->userRepository->find($userId)
            : null;

        // The code below is copied from Store (ChromaDb) and modified to accept filters
        $collection = $this->client->getOrCreateCollection($this->collectionName);
        $queryResponse = $collection->query(
            queryEmbeddings: [$vector->getData()],
            nResults: 4,
            where: [
                'owner' => [
                    '$eq' => $user?->getEmail() ?? '',
                ],
            ]
        );

        if (empty($queryResponse->metadatas) || empty($queryResponse->ids) || empty($queryResponse->embeddings)) {
            return [];
        }

        $documents = [];
        for ($i = 0; $i < count($queryResponse->metadatas[0]); ++$i) {
            $documents[] = new VectorDocument(
                id: Uuid::fromString($queryResponse->ids[0][$i]),
                /* @phpstan-ignore-next-line */
                vector: new Vector($queryResponse->embeddings[0][$i]),
                metadata: new Metadata($queryResponse->metadatas[0][$i]),
            );
        }

        return $documents;
    }
}
