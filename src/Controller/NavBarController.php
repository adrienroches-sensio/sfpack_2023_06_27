<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\Movie;
use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

final class NavBarController extends AbstractController
{
    public function main(MovieRepository $movieRepository): Response
    {
        $movies = Movie::fromEntities($movieRepository->listAll());

        return $this->render('navbar.html.twig', [
            'movies' => $movies,
        ]);
    }
}
