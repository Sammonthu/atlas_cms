<?php
// Déclare le namespace du contrôleur de sécurité.
namespace App\Controller;

// Importe le contrôleur abstrait Symfony.
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// Importe la réponse HTTP Symfony.
use Symfony\Component\HttpFoundation\Response;
// Importe l'attribut de route Symfony.
use Symfony\Component\Routing\Attribute\Route;
// Importe l'utilitaire d'authentification Symfony.
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

// Déclare le contrôleur de connexion/déconnexion.
class SecurityController extends AbstractController
{
    // Déclare la route de connexion.
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Vérifie si un utilisateur est déjà connecté.
        if ($this->getUser() !== null) {
            // Redirige les utilisateurs connectés vers l'accueil.
            return $this->redirectToRoute('home');
        }

        // Rend la page de connexion avec erreurs éventuelles.
        return $this->render('security/login.html.twig', [
            // Injecte la dernière erreur d'authentification.
            'error' => $authenticationUtils->getLastAuthenticationError(),
            // Injecte le dernier identifiant saisi.
            'last_username' => $authenticationUtils->getLastUsername(),
        ]);
    }

    // Déclare la route de déconnexion.
    #[Route('/logout', name: 'app_logout')]
    public function logout(): never
    {
        // Lève l'exception standard car Symfony intercepte cette route.
        throw new \LogicException('This method can be blank: it is intercepted by the firewall.');
    }
}

