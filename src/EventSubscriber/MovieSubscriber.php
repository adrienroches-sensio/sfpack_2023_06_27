<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Events\MovieCreatedEvent;
use App\Repository\UserRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function array_map;
use function implode;
use function sprintf;

class MovieSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UserRepository $userRepository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MovieCreatedEvent::class => [
                ['notifyAllAdmins', 0],
            ],
        ];
    }

    public function notifyAllAdmins(MovieCreatedEvent $event): void
    {
        $allAdmins = $this->userRepository->listAllAdmins();

        dump(sprintf(
            '%s user created the movie %s from %d on the %s. Will notify all those admins : %s.',
            $event->user->getUserIdentifier(),
            $event->movie->getTitle(),
            $event->movie->getReleasedAt()->format('Y'),
            $event->at->format('d/m/Y'),
            implode(', ', array_map(fn(User $user): string => $user->getUserIdentifier(), $allAdmins))
         ));
    }
}
