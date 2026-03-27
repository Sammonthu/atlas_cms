<?php
// Declare le namespace des controleurs API.
namespace App\Controller\Api;

// Importe le controleur abstrait Symfony.
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// Importe la reponse JSON Symfony.
use Symfony\Component\HttpFoundation\JsonResponse;
// Importe l'attribut de route Symfony.
use Symfony\Component\Routing\Attribute\Route;

// Declare un endpoint API de sante pour preparer l'extension REST.
#[Route('/api', name: 'api_')]
class HealthController extends AbstractController
{
    // Declare la route de verification API.
    #[Route('/health', name: 'health', methods: ['GET'])]
    public function health(): JsonResponse
    {
        // Retourne un payload JSON minimal pour supervision et tests.
        return $this->json([
            // Expose le statut de disponibilite de l'API.
            'status' => 'ok',
            // Expose le nom du projet.
            'application' => 'Atlas CMS',
            // Expose la date de reponse ISO-8601.
            'generatedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ]);
    }
}
