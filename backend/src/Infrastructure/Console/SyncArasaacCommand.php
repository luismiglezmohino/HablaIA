<?php

declare(strict_types=1);

namespace App\Infrastructure\Console;

use App\Domain\Category\Entity\Category;
use App\Domain\Category\Repository\CategoryRepository;
use App\Domain\Category\ValueObject\CategoryId;
use App\Domain\Pictogram\Entity\Pictogram;
use App\Domain\Pictogram\Repository\PictogramRepository;
use App\Domain\Pictogram\Service\PictogramProviderInterface;
use App\Domain\Pictogram\ValueObject\PictogramId;
use App\Domain\Shared\Service\UuidGeneratorInterface;
use App\Infrastructure\Service\ImageDownloaderInterface;
use App\Infrastructure\Service\VocabularyLoaderInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Synchronizes pictograms from ARASAAC API to local storage.
 *
 * This command downloads pictogram images and metadata from ARASAAC,
 * storing images locally and saving metadata to the database.
 *
 * Usage:
 *   - Sync specific keywords: app:arasaac:sync comer beber -c Acciones
 *   - Sync all vocabulary from YAML: app:arasaac:sync --all
 *   - Dry run: app:arasaac:sync --all --dry-run
 */
#[AsCommand(
    name: 'app:arasaac:sync',
    description: 'Synchronize pictograms from ARASAAC API'
)]
final class SyncArasaacCommand extends Command
{
    private const string LOCAL_PICTOGRAMS_PATH_PREFIX = '/pictograms';

    public function __construct(
        private readonly PictogramProviderInterface $pictogramProvider,
        private readonly PictogramRepository $pictogramRepository,
        private readonly CategoryRepository $categoryRepository,
        private readonly UuidGeneratorInterface $uuidGenerator,
        private readonly VocabularyLoaderInterface $vocabularyLoader,
        private readonly ImageDownloaderInterface $imageDownloader,
        private readonly string $pictogramsDirectory
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'keywords',
                InputArgument::IS_ARRAY | InputArgument::OPTIONAL,
                'Keywords to search for pictograms'
            )
            ->addOption(
                'category',
                'c',
                InputOption::VALUE_REQUIRED,
                'Category name to assign to pictograms'
            )
            ->addOption(
                'all',
                'a',
                InputOption::VALUE_NONE,
                'Sync vocabulary from YAML configuration file'
            )
            ->addOption(
                'dry-run',
                null,
                InputOption::VALUE_NONE,
                'Show what would be synced without saving or downloading'
            )
            ->addOption(
                'limit',
                'l',
                InputOption::VALUE_REQUIRED,
                'Maximum pictograms per keyword',
                1
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var array<string> $keywords */
        $keywords = $input->getArgument('keywords');

        /** @var string|null $categoryName */
        $categoryName = $input->getOption('category');

        /** @var bool $syncAll */
        $syncAll = (bool) $input->getOption('all');

        /** @var bool $dryRun */
        $dryRun = (bool) $input->getOption('dry-run');

        $limitValue = $input->getOption('limit');
        $limit = is_numeric($limitValue) ? (int) $limitValue : 1;

        if (empty($keywords) && !$syncAll) {
            $io->error('Please provide keywords or use --all option');

            return Command::FAILURE;
        }

        if (!empty($keywords) && $categoryName === null) {
            $io->error('Please specify a category with --category option');

            return Command::FAILURE;
        }

        if ($syncAll) {
            return $this->syncAllCategories($io, $dryRun, $limit);
        }

        /** @var string $categoryName - validated above */
        return $this->syncKeywords($io, $keywords, $categoryName, $dryRun, $limit);
    }

    /**
     * @param array<string> $keywords
     */
    private function syncKeywords(
        SymfonyStyle $io,
        array $keywords,
        string $categoryName,
        bool $dryRun,
        int $limit
    ): int {
        $category = $this->categoryRepository->findByName($categoryName);

        if ($category === null) {
            $io->error(sprintf('Category "%s" not found', $categoryName));

            return Command::FAILURE;
        }

        $syncedCount = $this->syncKeywordsForCategory($io, $keywords, $category, $dryRun, $limit);

        $this->displayCompletionMessage($io, $syncedCount, $dryRun);

        return Command::SUCCESS;
    }

    private function syncAllCategories(SymfonyStyle $io, bool $dryRun, int $limit): int
    {
        $io->info('Syncing vocabulary from YAML file');

        $vocabulary = $this->vocabularyLoader->load();
        $totalSynced = 0;

        foreach ($vocabulary as $categoryName => $keywords) {
            $category = $this->categoryRepository->findByName($categoryName);

            if ($category === null) {
                $io->warning(sprintf('Category "%s" not found, skipping', $categoryName));

                continue;
            }

            $io->section(sprintf('Syncing category: %s', $categoryName));
            $syncedCount = $this->syncKeywordsForCategory($io, $keywords, $category, $dryRun, $limit);
            $totalSynced += $syncedCount;
        }

        $this->displayCompletionMessage($io, $totalSynced, $dryRun);

        return Command::SUCCESS;
    }

    /**
     * @param array<string> $keywords
     */
    private function syncKeywordsForCategory(
        SymfonyStyle $io,
        array $keywords,
        Category $category,
        bool $dryRun,
        int $limit
    ): int {
        $syncedCount = 0;

        foreach ($keywords as $keyword) {
            $io->text(sprintf('Searching for "%s"...', $keyword));

            try {
                $syncedCount += $this->processKeyword($io, $keyword, $category, $dryRun, $limit);
            } catch (\Throwable $e) {
                $io->warning(sprintf('Warning: Failed to fetch "%s": %s', $keyword, $e->getMessage()));
            }
        }

        return $syncedCount;
    }

    private function processKeyword(
        SymfonyStyle $io,
        string $keyword,
        Category $category,
        bool $dryRun,
        int $limit
    ): int {
        $pictograms = $this->pictogramProvider->searchByKeyword($keyword, 'es');
        $pictogramsToSync = array_slice($pictograms, 0, $limit);
        $syncedCount = 0;

        foreach ($pictogramsToSync as $pictogram) {
            if ($dryRun) {
                $io->text(sprintf(
                    '[DRY-RUN] Would sync: %s (ARASAAC ID: %d)',
                    $pictogram->label(),
                    $pictogram->arasaacId()->value()
                ));

                continue;
            }

            if ($this->syncSinglePictogram($io, $pictogram, $category->id())) {
                $syncedCount++;
            }
        }

        return $syncedCount;
    }

    private function syncSinglePictogram(
        SymfonyStyle $io,
        Pictogram $pictogram,
        CategoryId $categoryId
    ): bool {
        $arasaacId = $pictogram->arasaacId()->value();
        $localImagePath = $this->buildLocalImagePath($arasaacId);
        $fullLocalPath = $this->buildFullLocalPath($arasaacId);

        if ($this->imageDownloader->exists($fullLocalPath)) {
            $io->text(sprintf('Image already exists: %s', $localImagePath));
        } else {
            $downloadSuccess = $this->imageDownloader->download(
                $pictogram->imagePath(),
                $fullLocalPath
            );

            if (!$downloadSuccess) {
                $io->warning(sprintf('Failed to download image for "%s"', $pictogram->label()));

                return false;
            }
        }

        $newPictogram = $this->createPictogramWithLocalPath($pictogram, $categoryId, $localImagePath);
        $this->pictogramRepository->save($newPictogram);

        return true;
    }

    private function buildLocalImagePath(int $arasaacId): string
    {
        return sprintf('%s/%d.png', self::LOCAL_PICTOGRAMS_PATH_PREFIX, $arasaacId);
    }

    private function buildFullLocalPath(int $arasaacId): string
    {
        return sprintf('%s/%d.png', $this->pictogramsDirectory, $arasaacId);
    }

    private function createPictogramWithLocalPath(
        Pictogram $pictogram,
        CategoryId $categoryId,
        string $localImagePath
    ): Pictogram {
        return new Pictogram(
            PictogramId::fromString($this->uuidGenerator->generate()),
            $pictogram->arasaacId(),
            $categoryId,
            $pictogram->label(),
            $localImagePath
        );
    }

    private function displayCompletionMessage(SymfonyStyle $io, int $syncedCount, bool $dryRun): void
    {
        $prefix = $dryRun ? '[DRY-RUN] ' : '';
        $io->success(sprintf('%sSync completed. Synced %d pictogram(s)', $prefix, $syncedCount));
    }
}
