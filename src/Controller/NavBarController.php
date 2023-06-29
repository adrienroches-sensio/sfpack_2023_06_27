<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class NavBarController extends AbstractController
{
    public function __construct(
        private readonly MovieRepository $movieRepository,
    ) {
    }

    public function main(): Response
    {
        return $this->render('navbar.html.twig', [
            'movies' => $this->movieRepository->listOnlySlugAndTitles(),
        ]);
    }
}
