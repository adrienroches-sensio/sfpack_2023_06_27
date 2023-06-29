<?php

namespace App\Command;

use App\Entity\Movie as MovieEntity;
use App\Omdb\Bridge\OmdbToDatabaseImporterInterface;
use App\Omdb\Client\Model\SearchResult;
use App\Omdb\Client\NoResult;
use App\Omdb\Client\OmdbApiClientInterface;
use App\Repository\MovieRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use function array_reduce;
use function count;
use function sprintf;

#[AsCommand(
    name: 'app:movie:import',
    description: 'Search and import one or more movies into the database.',
    aliases: [
        'movie:import',
        'omdb:movie:import',
    ]
)]
class MovieImportCommand extends Command
{
    public function __construct(
        private readonly MovieRepository $movieRepository,
        private readonly OmdbApiClientInterface $omdbApiClient,
        private readonly OmdbToDatabaseImporterInterface $omdbToDatabaseImporter,
    ) {
        parent::__construct(null);
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id-or-title', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'IMDB ID or title to search.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Will not import into database. Only displays what would happen.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Import movies from OMDB');

        $idOrTitles = $input->getArgument('id-or-title');
        $io->writeln(sprintf('Trying to import %d movies into the database.', count($idOrTitles)));

        /** @var list<array{string, MovieEntity}> $success */
        $success = [];

        /** @var list<string> $failure */
        $failure = [];

        foreach ($idOrTitles as $idOrTitle) {
            $movieEntity = $this->tryImport($io, $idOrTitle);

            if (null !== $movieEntity) {
                $success[] = [$idOrTitle, $movieEntity];
            } else {
                $failure[] = $idOrTitle;
            }
        }

        $isDryRun = $input->getOption('dry-run');

        if (false === $isDryRun) {
            $this->movieRepository->flush();
        }

        if ([] !== $success) {
            $io->success('The following movies were imported.');
            $io->table(
                ['ID', 'Title', 'Query'],
                array_reduce($success, static function(array $rows, array $success): array {
                    /** @var string $query */
                    /** @var MovieEntity $movieEntity */
                    [$query, $movieEntity] = $success;

                    $rows[] = [
                        $movieEntity->getId(),
                        "{$movieEntity->getTitle()} ({$movieEntity->getReleasedAt()->format('Y')})",
                        $query
                    ];

                    return $rows;
                }, [])
            );
        }

        if ([] !== $failure) {
            $io->warning('The following terms were not conclusive.');
            $io->listing($failure);
        }

        return Command::SUCCESS;
    }

    private function tryImport(SymfonyStyle $io, string $idOrTitle): MovieEntity|null
    {
        $io->section("Trying >>> {$idOrTitle}");

        return $this->tryImportAsImdbId($io, $idOrTitle)
            ?? $this->searchAndImportAsTitle($io, $idOrTitle);
    }

    private function tryImportAsImdbId(SymfonyStyle $io, string $imdbId): MovieEntity|null
    {
        try {
            $movieOmdb = $this->omdbApiClient->getById($imdbId);
        } catch (NoResult) {
            return null;
        }

        return $this->omdbToDatabaseImporter->importToDatabase($movieOmdb, false);
    }

    private function searchAndImportAsTitle(SymfonyStyle $io, string $title): MovieEntity|null
    {
        try {
            $searchResults = $this->omdbApiClient->searchByTitle($title);
        } catch (NoResult) {
            return null;
        }

        if (count($searchResults) === 1) {
            $import = $io->askQuestion(new ConfirmationQuestion("Do you want to import '{$searchResults[0]->Title} ({$searchResults[0]->Year})'"));

            if (true === $import) {
                return $this->tryImportAsImdbId($io, $searchResults[0]->imdbId);
            }

            return null;
        }

        $choices = array_reduce($searchResults, static function (array $choices, SearchResult $searchResult): array {
            $choices[$searchResult->imdbId] = "{$searchResult->Title} ({$searchResult->Year})";

            return $choices;
        }, []);

        $choices['none'] = 'None of the above.';

        $selectedChoice = $io->choice('Which movie would you like to import ?', $choices);

        if ('none' === $selectedChoice) {
            return null;
        }

        return $this->tryImportAsImdbId($io, $selectedChoice);
    }
}
