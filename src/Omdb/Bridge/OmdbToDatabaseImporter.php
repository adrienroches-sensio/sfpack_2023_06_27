<?php

declare(strict_types=1);

namespace App\Omdb\Bridge;

use App\Entity\Movie as MovieEntity;
use App\Model\Rated;
use App\Omdb\Client\Model\Movie as MovieOmdb;
use App\Repository\GenreRepository;
use App\Repository\MovieRepository;
use DateTimeImmutable;
use Symfony\Component\String\Slugger\SluggerInterface;
use function explode;

final class OmdbToDatabaseImporter implements OmdbToDatabaseImporterInterface
{
    public function __construct(
        private readonly MovieRepository $movieRepository,
        private readonly GenreRepository $genreRepository,
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function importToDatabase(MovieOmdb $movieOmdb, bool $flush = false): MovieEntity
    {
        $movieEntity = (new MovieEntity())
            ->setTitle($movieOmdb->Title)
            ->setPlot($movieOmdb->Plot)
            ->setReleasedAt(new DateTimeImmutable($movieOmdb->Released))
            ->setPoster($movieOmdb->Poster)
            ->setRated(Rated::tryFrom($movieOmdb->Rated))
            ->setSlug($this->slugger->slug("{$movieOmdb->Year} {$movieOmdb->Title}")->toString())
        ;

        foreach (explode(', ', $movieOmdb->Genre) as $genreName) {
            $genre = $this->genreRepository->get($genreName);

            $movieEntity->addGenre($genre);
        }

        $this->movieRepository->save($movieEntity, $flush);

        return $movieEntity;
    }
}
