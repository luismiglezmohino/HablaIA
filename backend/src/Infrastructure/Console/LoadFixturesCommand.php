<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Infrastructure\DataFixtures\CategoryFixtures;
use App\Infrastructure\DataFixtures\CategoryFixturesInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fixtures:load',
    description: 'Load SAAC standard categories into the database'
)]
final class LoadFixturesCommand extends Command
{
    public function __construct(
        private readonly CategoryFixturesInterface $categoryFixtures,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Show categories that would be loaded without actually loading them'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $isDryRun = (bool) $input->getOption('dry-run');

        if ($isDryRun) {
            $this->executeDryRun($io);
            return Command::SUCCESS;
        }

        $count = $this->categoryFixtures->load();

        $io->success(sprintf('Loaded %d categories successfully.', $count));

        return Command::SUCCESS;
    }

    private function executeDryRun(SymfonyStyle $io): void
    {
        $io->title('[dry-run] Categories that would be loaded:');

        $categories = CategoryFixtures::getCategories();

        $tableRows = [];
        foreach ($categories as $category) {
            $tableRows[] = [$category['name'], $category['icon']];
        }

        $io->table(['Name', 'Icon'], $tableRows);

        $io->info(sprintf('Total: %d categories (dry-run mode - no changes made)', count($categories)));
    }
}
