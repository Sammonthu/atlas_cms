<?php
// Declare le namespace du controleur d'accueil.
namespace App\Controller;

// Importe l'entite Article.
use App\Entity\Article;
// Importe l'entite Gallery.
use App\Entity\Gallery;
// Importe l'entite Page.
use App\Entity\Page;
// Importe l'EntityManager Doctrine.
use Doctrine\ORM\EntityManagerInterface;
// Importe le controleur abstrait Symfony.
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// Importe la reponse HTTP Symfony.
use Symfony\Component\HttpFoundation\Response;
// Importe l'attribut de route Symfony.
use Symfony\Component\Routing\Attribute\Route;

// Declare le controleur front de la page d'accueil.
class HomeController extends AbstractController
{
    // Declare la route de la page d'accueil.
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Recupere les dernieres pages validees.
        $latestPages = $entityManager->getRepository(Page::class)->findBy(
            ['moderationStatus' => 'approved'],
            ['updatedAt' => 'DESC'],
            3
        );

        // Recupere les derniers articles valides.
        $latestArticles = $entityManager->getRepository(Article::class)->findBy(
            ['moderationStatus' => 'approved'],
            ['publishedAt' => 'DESC', 'createdAt' => 'DESC'],
            6
        );

        // Recupere les dernieres galeries.
        $latestGalleries = $entityManager->getRepository(Gallery::class)->findBy(
            [],
            ['updatedAt' => 'DESC'],
            4
        );

        // Construit la reponse Twig de la home.
        $response = $this->render('front/home/index.html.twig', [
            // Passe les pages recentes.
            'latestPages' => $latestPages,
            // Passe les articles recents.
            'latestArticles' => $latestArticles,
            // Passe les galeries recentes.
            'latestGalleries' => $latestGalleries,
        ]);

        // Active un cache navigateur prive de courte duree.
        $response->setPrivate();
        // Definit la duree de cache navigateur en secondes.
        $response->setMaxAge(180);

        // Retourne la reponse finale.
        return $response;
    }
}
