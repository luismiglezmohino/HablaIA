<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use Cycle\Database\DatabaseManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:migrations:run',
    description: 'Run pending SQL migrations from backend/migrations/'
)]
final class RunMigrationsCommand extends Command
{
    private const string MIGRATIONS_TABLE = 'schema_migrations';

    public function __construct(
        private readonly DatabaseManager $dbal,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'dry-run',
            null,
            InputOption::VALUE_NONE,
            'Show pending migrations without executing them'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $isDryRun = (bool) $input->getOption('dry-run');
        $db = $this->dbal->database();

        $this->ensureMigrationsTable($db);

        $applied = $this->getAppliedMigrations($db);
        $files = $this->getMigrationFiles();
        $pending = array_diff_key($files, array_flip($applied));

        if (empty($pending)) {
            $io->success('No pending migrations.');
            return Command::SUCCESS;
        }

        ksort($pending);

        if ($isDryRun) {
            $io->title('[dry-run] Pending migrations:');
            foreach (array_keys($pending) as $name) {
                $io->writeln("  - {$name}");
            }
            $io->info(sprintf('%d migration(s) would be applied.', count($pending)));
            return Command::SUCCESS;
        }

        foreach ($pending as $name => $path) {
            $io->write("Applying {$name}... ");

            $sql = $this->extractUpSql($path);
            if (empty(trim($sql))) {
                $io->writeln('<comment>SKIP (empty)</comment>');
                continue;
            }

            $db->execute($sql);
            $db->execute(
                sprintf("INSERT INTO %s (version, applied_at) VALUES ('%s', NOW())", self::MIGRATIONS_TABLE, $name)
            );

            $io->writeln('<info>OK</info>');
        }

        $io->success(sprintf('Applied %d migration(s).', count($pending)));

        return Command::SUCCESS;
    }

    private function ensureMigrationsTable(mixed $db): void
    {
        $db->execute(sprintf(
            'CREATE TABLE IF NOT EXISTS %s (
                version VARCHAR(255) NOT NULL PRIMARY KEY,
                applied_at TIMESTAMP NOT NULL DEFAULT NOW()
            )',
            self::MIGRATIONS_TABLE
        ));
    }

    /** @return string[] */
    private function getAppliedMigrations(mixed $db): array
    {
        $rows = $db->query(sprintf('SELECT version FROM %s ORDER BY version', self::MIGRATIONS_TABLE))->fetchAll();

        return array_column($rows, 'version');
    }

    /** @return array<string, string> name => path */
    private function getMigrationFiles(): array
    {
        $dir = dirname(__DIR__, 3) . '/migrations';
        if (!is_dir($dir)) {
            return [];
        }

        $files = glob($dir . '/V*.sql');
        if ($files === false) {
            return [];
        }

        $result = [];
        foreach ($files as $path) {
            $result[basename($path, '.sql')] = $path;
        }

        ksort($result);

        return $result;
    }

    private function extractUpSql(string $path): string
    {
        $content = file_get_contents($path);
        if ($content === false) {
            return '';
        }

        $upPos = strpos($content, '-- UP');
        $downPos = strpos($content, '-- DOWN');

        if ($upPos === false) {
            return $content;
        }

        $start = $upPos + strlen('-- UP');
        $length = $downPos !== false ? $downPos - $start : null;

        return $length !== null ? substr($content, $start, $length) : substr($content, $start);
    }
}
