<?php

declare(strict_types=1);

namespace App\Events;

use App\Entity\Movie;
use App\Entity\User;
use DateTimeImmutable;
use Symfony\Contracts\EventDispatcher\Event;

final class MovieCreatedEvent extends Event
{
    public function __construct(
        public readonly Movie             $movie,
        public readonly User              $user,
        public readonly DateTimeImmutable $at,
    ) {
    }
}
