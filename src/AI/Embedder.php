<?php

namespace App\AI;

use App\Repository\OrderRepository;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\TextDocument;
use Symfony\AI\Store\Indexer;
use Symfony\Component\Uid\Uuid;

final readonly class Embedder
{
    public function __construct(
        private Indexer $indexer,
        private OrderRepository $orderRepository,
    ) {
    }

    public function embed(): void
    {
        $documents = [];

        foreach ($this->orderRepository->findAll() as $order) {
            $documents[] = new TextDocument(
                Uuid::v4(),
                (string) $order,
                new Metadata($order->toArray())
            );
        }

        $this->indexer->index($documents);
    }
}
