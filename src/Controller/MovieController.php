<?php

namespace App\Controller;

use App\Entity\Movie as MovieEntity;
use App\Entity\User;
use App\Events\MovieCreatedEvent;
use App\Form\MovieType;
use App\Model\Movie;
use App\Omdb\Client\OmdbApiClientInterface;
use App\Repository\MovieRepository;
use App\Security\Voter\MovieVoter;
use Psr\Clock\ClockInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @method User getUser()
 */
class MovieController extends AbstractController
{
    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly ClockInterface $clock,
        private readonly MovieRepository $movieRepository,
        private readonly OmdbApiClientInterface $omdbApiClient,
    ) {
    }

    #[Route(
        path: '/movies',
        name: 'app_movies_list',
        methods: ['GET'],
    )]
    public function list(): Response
    {
        $movies = Movie::fromEntities($this->movieRepository->listAll());

        return $this->render('movie/list.html.twig', [
            'movies' => $movies,
        ]);
    }

    #[Route(
        path: '/movies/{slug}',
        name: 'app_movies_details',
        requirements: [
            'slug' => MovieEntity::SLUG_FORMAT,
        ],
        methods: ['GET'],
    )]
    public function details(string $slug): Response
    {
        $movie = Movie::fromEntity($this->movieRepository->getBySlug($slug));

        $this->denyAccessUnlessGranted(MovieVoter::MOVIE_DETAILS, $movie);

        return $this->render('movie/details.html.twig', [
            'movie' => $movie,
        ]);
    }

    #[Route(
        path: '/movies/{imdbId}',
        name: 'app_movies_omdb_details',
        requirements: [
            'imdbId' => 'tt.+',
        ],
        methods: ['GET'],
    )]
    public function omdbDetails(string $imdbId): Response
    {
        $movie = Movie::fromOmdb($this->omdbApiClient->getById($imdbId));

        $this->denyAccessUnlessGranted(MovieVoter::MOVIE_DETAILS, $movie);

        return $this->render('movie/details.html.twig', [
            'movie' => $movie,
        ]);
    }

    #[Route(
        path: '/admin/movies/new',
        name: 'app_movies_new',
        methods: ['GET', 'POST'],
    )]
    #[Route(
        path: '/admin/movies/{slug}/edit',
        name: 'app_movies_edit',
        requirements: [
            'slug' => MovieEntity::SLUG_FORMAT,
        ],
        methods: ['GET', 'POST'],
    )]
    public function newOrEdit(
        Request $request,
        string|null $slug = null
    ): Response {
        $isNew = null === $slug;

        $movie = new MovieEntity();
        if (null !== $slug) {
            $movie = $this->movieRepository->getBySlug($slug);
        }

        $form = $this->createForm(MovieType::class, $movie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->movieRepository->save($movie, true);

            if (true === $isNew) {
                $this->eventDispatcher->dispatch(
                    new MovieCreatedEvent(
                        $movie,
                        $this->getUser(),
                        $this->clock->now(),
                    )
                );
            }

            return $this->redirectToRoute('app_movies_details', [
                'slug' => $movie->getSlug()
            ]);
        }

        return $this->render('movie/new.html.twig', [
            'movie_form' => $form,
        ]);
    }
}
