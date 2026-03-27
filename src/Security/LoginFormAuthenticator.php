<?php
// Déclare le namespace de l'authenticator.
namespace App\Security;

// Importe la classe abstraite de login form Symfony.
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
// Importe la classe Passport Symfony.
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
// Importe le badge utilisateur Symfony.
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
// Importe les credentials mot de passe Symfony.
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
// Importe le badge CSRF Symfony.
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
// Importe le trait de target path Symfony.
use Symfony\Component\Security\Http\Util\TargetPathTrait;
// Importe la requête HTTP Symfony.
use Symfony\Component\HttpFoundation\Request;
// Importe la réponse HTTP Symfony.
use Symfony\Component\HttpFoundation\Response;
// Importe la redirection HTTP Symfony.
use Symfony\Component\HttpFoundation\RedirectResponse;
// Importe l'interface token de sécurité Symfony.
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
// Importe le générateur d'URL Symfony.
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

// Déclare l'authenticator de connexion formulaire.
class LoginFormAuthenticator extends AbstractLoginFormAuthenticator
{
    // Déclare la constante de route de connexion.
    public const LOGIN_ROUTE = 'app_login';

    // Active la gestion native des redirections de cible.
    use TargetPathTrait;

    // Injecte le générateur d'URL Symfony.
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
        // Ne réalise aucun traitement complémentaire au constructeur.
    }

    // Construit le passport d'authentification à partir de la requête.
    public function authenticate(Request $request): Passport
    {
        // Récupère l'email soumis.
        $email = (string) $request->request->get('email', '');
        // Récupère le mot de passe soumis.
        $password = (string) $request->request->get('password', '');
        // Récupère le token CSRF soumis.
        $csrfToken = (string) $request->request->get('_csrf_token', '');

        // Stocke le dernier identifiant saisi pour l'UI.
        $request->getSession()->set('_security.last_username', $email);

        // Retourne un passport avec user, password et CSRF.
        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [new CsrfTokenBadge('authenticate', $csrfToken)]
        );
    }

    // Gère la redirection après connexion réussie.
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Vérifie une route cible sauvegardée par Symfony.
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            // Redirige vers la cible initiale.
            return new RedirectResponse($targetPath);
        }

        // Récupère les rôles effectifs du token connecté.
        $roles = $token->getRoleNames();
        // Vérifie si l'utilisateur est admin ou éditeur.
        if (in_array('ROLE_ADMIN', $roles, true) || in_array('ROLE_EDITOR', $roles, true)) {
            // Redirige vers le back-office.
            return new RedirectResponse($this->urlGenerator->generate('admin'));
        }

        // Redirige les autres profils vers le front.
        return new RedirectResponse($this->urlGenerator->generate('home'));
    }

    // Retourne l'URL de la page de connexion.
    protected function getLoginUrl(Request $request): string
    {
        // Génère l'URL de la route de connexion.
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}

