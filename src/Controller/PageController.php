<?php
// Declare le namespace du controleur des pages publiques.
namespace App\Controller;

// Importe l'entite Page.
use App\Entity\Page;
// Importe l'EntityManager Doctrine.
use Doctrine\ORM\EntityManagerInterface;
// Importe le controleur abstrait Symfony.
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// Importe l'attribut MapEntity Doctrine bridge.
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
// Importe la reponse HTTP Symfony.
use Symfony\Component\HttpFoundation\Response;
// Importe l'attribut de route Symfony.
use Symfony\Component\Routing\Attribute\Route;

// Declare le controleur front des pages CMS.
class PageController extends AbstractController
{
    // Declare la route de listing des pages publiques.
    #[Route('/pages', name: 'front_page_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Recupere toutes les pages validees.
        $pages = $entityManager->getRepository(Page::class)->findBy(
            ['moderationStatus' => 'approved'],
            ['updatedAt' => 'DESC']
        );

        // Construit la reponse Twig de la liste.
        $response = $this->render('front/page/index.html.twig', [
            // Passe les pages validees a Twig.
            'pages' => $pages,
        ]);

        // Active un cache navigateur prive de courte duree.
        $response->setPrivate();
        // Definit la duree de cache navigateur en secondes.
        $response->setMaxAge(180);

        // Retourne la reponse finale.
        return $response;
    }

    // Declare la route de detail d'une page.
    #[Route('/page/{slug}', name: 'front_page_show', methods: ['GET'])]
    public function show(#[MapEntity(mapping: ['slug' => 'slug'])] Page $page): Response
    {
        // Verifie que la page est validee.
        if ($page->getModerationStatus() !== 'approved') {
            // Retourne une 404 pour eviter l'exposition de contenu non valide.
            throw $this->createNotFoundException('Page indisponible.');
        }

        // Construit la reponse Twig du detail.
        $response = $this->render('front/page/show.html.twig', [
            // Passe l'entite page au template.
            'page' => $page,
        ]);

        // Active un cache navigateur prive de courte duree.
        $response->setPrivate();
        // Definit la duree de cache navigateur en secondes.
        $response->setMaxAge(180);

        // Retourne la reponse finale.
        return $response;
    }
}
