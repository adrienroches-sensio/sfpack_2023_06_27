<?php

namespace App\Controller;

use App\Form\MovieType;
use App\Model\Movie;
use App\Repository\MovieRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MovieController extends AbstractController
{
    #[Route(
        path: '/movies',
        name: 'app_movies_list',
        methods: ['GET'],
    )]
    public function list(MovieRepository $movieRepository): Response
    {
        $movies = Movie::fromEntities($movieRepository->listAll());

        return $this->render('movie/list.html.twig', [
            'movies' => $movies,
        ]);
    }

    #[Route(
        path: '/movies/{slug}',
        name: 'app_movies_details',
        requirements: [
            'slug' => '\d{4}-\w([-\w])*'
        ],
        methods: ['GET'],
    )]
    public function details(MovieRepository $movieRepository, string $slug): Response
    {
        $movie = Movie::fromEntity($movieRepository->getBySlug($slug));

        return $this->render('movie/details.html.twig', [
            'movie' => $movie,
        ]);
    }

    #[Route(
        path: '/movies/new',
        name: 'app_movies_new',
        methods: ['GET'],
    )]
    public function new(): Response
    {
        $form = $this->createForm(MovieType::class);

        return $this->render('movie/new.html.twig', [
            'movie_form' => $form,
        ]);
    }
}
