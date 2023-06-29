<?php

declare(strict_types=1);

namespace App\Omdb\Client;

use App\Omdb\Client\Model\Movie;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class OmdbApiClient implements OmdbApiClientInterface
{
    public function __construct(
        private readonly HttpClientInterface $omdbApiClient,
    ) {
    }

    public function getById(string $imdbId): Movie
    {
        $response = $this->omdbApiClient->request('GET', '/', [
            'query' => [
                'i' => $imdbId,
                'plot' => 'full',
            ]
        ]);

        $movieRaw = $response->toArray();

        return new Movie(
            Title: $movieRaw['Title'],
            Year: $movieRaw['Year'],
            Rated: $movieRaw['Rated'],
            Released: $movieRaw['Released'],
            Genre: $movieRaw['Genre'],
            Plot: $movieRaw['Plot'],
            Poster: $movieRaw['Poster'],
            imdbID: $movieRaw['imdbID'],
            Type: $movieRaw['Type'],
        );
    }
}
