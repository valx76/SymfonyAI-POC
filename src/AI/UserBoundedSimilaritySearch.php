<?php

namespace App\AI;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Platform\Model;
use Symfony\AI\Platform\PlatformInterface;
use Symfony\AI\Store\Document\VectorDocument;
use Symfony\AI\Store\StoreInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsTool('user_bounded_similarity_search', description: 'Searches for documents similar to a query or sentence, bounded to a specific user.')]
final class UserBoundedSimilaritySearch
{
    /**
     * @var VectorDocument[]
     */
    public array $usedDocuments = [];

    public function __construct(
        private readonly PlatformInterface $platform,
        private readonly Model $model,
        #[Autowire(service: 'ai.store.chroma_db.app')]
        private readonly StoreInterface $store,
        private readonly RequestStack $requestStack,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @param string $searchTerm string used for similarity search
     */
    public function __invoke(string $searchTerm): string
    {
        $vectors = $this->platform->invoke($this->model, $searchTerm)->asVectors();
        $this->usedDocuments = $this->store->query($vectors[0], [
            'where' => [
                'owner' => [
                    '$eq' => $this->getUser()?->getEmail() ?? '',
                ],
            ],
        ]);

        if ([] === $this->usedDocuments) {
            return 'No results found';
        }

        $result = 'Found documents with following information:'.\PHP_EOL;
        foreach ($this->usedDocuments as $document) {
            $result .= json_encode($document->metadata);
        }

        return $result;
    }

    private function getUser(): ?User
    {
        /** @var ?int $userId */
        $userId = $this->requestStack->getSession()->get('userId');

        return null !== $userId
            ? $this->userRepository->find($userId)
            : null;
    }
}
