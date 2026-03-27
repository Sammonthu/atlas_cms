<?php
// Declare le namespace des subscribers HTTP.
namespace App\EventSubscriber;

// Importe l'evenement de reponse kernel.
use Symfony\Component\HttpKernel\Event\ResponseEvent;
// Importe l'interface EventSubscriber Symfony.
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

// Declare un subscriber pour appliquer des en-tetes de securite HTTP.
class SecurityHeadersSubscriber implements EventSubscriberInterface
{
    // Retourne les evenements ecoutes par ce subscriber.
    public static function getSubscribedEvents(): array
    {
        // Ecoute l'evenement kernel.response.
        return ['kernel.response' => 'onKernelResponse'];
    }

    // Ajoute des en-tetes de securite a chaque reponse HTML.
    public function onKernelResponse(ResponseEvent $event): void
    {
        // Ignore les sous-requetes pour eviter les doublons.
        if (!$event->isMainRequest()) {
            // Sort si ce n'est pas la requete principale.
            return;
        }

        // Recupere la reponse HTTP courante.
        $response = $event->getResponse();
        // Recupere les en-tetes de la reponse.
        $headers = $response->headers;

        // Ajoute une protection anti clickjacking.
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        // Ajoute une protection sniff MIME.
        $headers->set('X-Content-Type-Options', 'nosniff');
        // Ajoute une politique de fuite de referer restrictive.
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        // Ajoute une politique de permissions navigateur minimale.
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Construit une CSP compatible avec Bootstrap CDN et assets locaux.
        $contentSecurityPolicy = "default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; img-src 'self' data: blob:; font-src 'self' https://cdn.jsdelivr.net data:; connect-src 'self'; object-src 'none'; base-uri 'self'; frame-ancestors 'self'; form-action 'self'";
        // Injecte la CSP dans la reponse.
        $headers->set('Content-Security-Policy', $contentSecurityPolicy);
    }
}
