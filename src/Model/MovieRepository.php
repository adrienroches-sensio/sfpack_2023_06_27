<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use function array_column;
use function array_map;

/**
 * @phpstan-type MovieRaw array{
 *     slug: string,
 *     title: string,
 *     plot: string,
 *     releasedAt: string,
 *     genres: list<string>
 * }
 */
final class MovieRepository
{
    /**
     * @var list<MovieRaw>
     */
    public final const MOVIES = [
        [
            'slug' => '2015-une-merveille-histoire-du-temps',
            'title' => 'Une Merveilleuse Histoire du Temps',
            'plot' => '1963, en Angleterre, Stephen, brillant étudiant en Cosmologie à l’Université de Cambridge, entend bien donner une réponse simple et efficace au mystère de la création de l’univers. De nouveaux horizons s’ouvrent quand il tombe amoureux d’une étudiante en art, Jane Wilde. Mais le jeune homme, alors dans la fleur de l’âge, se heurte à un diagnostic implacable : une dystrophie neuromusculaire plus connue sous le nom de maladie de Charcot va s’attaquer à ses membres, sa motricité, et son élocution, et finira par le tuer en l’espace de deux ans. Grâce à l’amour indéfectible, le courage et la résolution de Jane, qu’il épouse contre toute attente, ils entament tous les deux un nouveau combat afin de repousser l’inéluctable. Jane l’encourage à terminer son doctorat, et alors qu’ils commencent une vie de famille, Stephen, doctorat en poche va s’attaquer aux recherches sur ce qu’il a de plus précieux : le temps. Alors que son corps se dégrade, son cerveau fait reculer les frontières les plus éloignées de la physique. Ensemble, ils vont révolutionner le monde de la médecine et de la science, pour aller au-delà de ce qu’ils auraient pu imaginer : le vingt et unième siècle.',
            'releasedAt' => '21 Jan 2015',
            'genres' => ['Biopic', 'Drame'],
        ]
    ];

    /**
     * @param MovieRaw $raw
     */
    private static function hydrate(array $raw): Movie
    {
        return new Movie(
            slug: $raw['slug'],
            title: $raw['title'],
            plot: $raw['plot'],
            releasedAt: new DateTimeImmutable($raw['releasedAt']),
            genres: $raw['genres']
        );
    }

    public static function getBySlug(string $slug): Movie
    {
        $indexedBySlug = array_column(self::MOVIES, null, 'slug');

        return self::hydrate($indexedBySlug[$slug]);
    }

    /**
     * @return list<Movie>
     */
    public static function listAll(): array
    {
        return array_map(self::hydrate(...), self::MOVIES);
    }
}
