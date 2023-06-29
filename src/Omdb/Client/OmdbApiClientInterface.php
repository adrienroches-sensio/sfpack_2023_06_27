<?php

declare(strict_types=1);

namespace App\Omdb\Client;

use App\Omdb\Client\Model\Movie;

interface OmdbApiClientInterface
{
    public function getById(string $imdbId): Movie;
}
