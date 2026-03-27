<?php
// Declare le namespace du controleur galerie public.
namespace App\Controller;

// Importe l'entite Gallery.
use App\Entity\Gallery;
// Importe l'EntityManager Doctrine.
use Doctrine\ORM\EntityManagerInterface;
// Importe le controleur abstrait Symfony.
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// Importe la reponse HTTP Symfony.
use Symfony\Component\HttpFoundation\Response;
// Importe l'attribut de route Symfony.
use Symfony\Component\Routing\Attribute\Route;

// Declare le controleur front des galeries.
class GalleryController extends AbstractController
{
    // Declare la route de liste des galeries.
    #[Route('/galeries', name: 'front_gallery_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Recupere les galeries triees par mise a jour.
        $galleries = $entityManager->getRepository(Gallery::class)->findBy(
            [],
            ['updatedAt' => 'DESC']
        );

        // Construit la reponse Twig de la liste.
        $response = $this->render('front/gallery/index.html.twig', [
            // Passe les galeries a Twig.
            'galleries' => $galleries,
        ]);

        // Active un cache navigateur prive de courte duree.
        $response->setPrivate();
        // Definit la duree de cache navigateur en secondes.
        $response->setMaxAge(180);

        // Retourne la reponse finale.
        return $response;
    }

    // Declare la route de detail galerie avec slug SEO lisible.
    #[Route('/galeries/{id}-{slug}', name: 'front_gallery_show', methods: ['GET'], requirements: ['id' => '\\d+'])]
    public function show(Gallery $gallery): Response
    {
        // Construit la reponse Twig du detail.
        $response = $this->render('front/gallery/show.html.twig', [
            // Passe la galerie au template.
            'gallery' => $gallery,
        ]);

        // Active un cache navigateur prive de courte duree.
        $response->setPrivate();
        // Definit la duree de cache navigateur en secondes.
        $response->setMaxAge(180);

        // Retourne la reponse finale.
        return $response;
    }
}
