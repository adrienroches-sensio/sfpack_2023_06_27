<?php

declare(strict_types=1);

namespace App\Omdb\Client;

use App\Omdb\Client\Model\Movie;

interface OmdbApiClientInterface
{
    /**
     * @throws NoResult When the $imdbId was not found.
     */
    public function getById(string $imdbId): Movie;
}
