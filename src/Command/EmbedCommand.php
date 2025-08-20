<?php

namespace App\Command;

use App\AI\Embedder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand('app:embed', description: 'Create embeddings for orders and push them to ChromaDB.')]
final class EmbedCommand extends Command
{
    public function __construct(
        private readonly Embedder $embedder,
    ) {
        parent::__construct();
    }

    public function __invoke(
        SymfonyStyle $io,
    ): int {
        $io->title('Loading orders as embeddings into ChromaDB');

        $this->embedder->embed();
        $io->success('Embeddings successfully saved to ChromaDB!');

        return Command::SUCCESS;
    }
}
