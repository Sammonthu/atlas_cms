<?php
// Declare le namespace du controleur de securite.
namespace App\Controller;

// Importe le controleur abstrait Symfony.
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// Importe la reponse HTTP Symfony.
use Symfony\Component\HttpFoundation\Response;
// Importe l'attribut de route Symfony.
use Symfony\Component\Routing\Attribute\Route;
// Importe l'utilitaire d'authentification Symfony.
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

// Declare le controleur de connexion/deconnexion.
class SecurityController extends AbstractController
{
    // Declare la route de connexion.
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Rend la page de connexion avec erreurs eventuelles.
        return $this->render('security/login.html.twig', [
            // Injecte la derniere erreur d'authentification.
            'error' => $authenticationUtils->getLastAuthenticationError(),
            // Injecte le dernier identifiant saisi.
            'last_username' => $authenticationUtils->getLastUsername(),
            // Masque le chrome global pour eviter les resolutions de token inutiles.
            'hideLayoutChrome' => true,
        ]);
    }

    // Declare la route de deconnexion.
    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        // Leve l'exception standard car Symfony intercepte cette route.
        throw new \LogicException('This method can be blank: it is intercepted by the firewall.');
    }
}
