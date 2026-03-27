<?php
// Declare le namespace du service.
namespace App\Service;

// Declare un service de sanitation HTML simple pour contenus CMS.
class HtmlContentSanitizer
{
    // Declare la liste blanche des balises HTML autorisees.
    private const string ALLOWED_TAGS = '<p><br><strong><em><ul><ol><li><a><h2><h3><h4><blockquote>';

    // Nettoie un contenu riche en conservant un sous-ensemble HTML controle.
    public function sanitizeRichText(string $html): string
    {
        // Supprime les balises non autorisees en conservant la liste blanche.
        $sanitizedHtml = strip_tags($html, self::ALLOWED_TAGS);
        // Supprime tous les attributs d'evenement JavaScript inline.
        $sanitizedHtml = preg_replace('/\son[a-z]+\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $sanitizedHtml) ?? $sanitizedHtml;
        // Neutralise les protocoles javascript dans les attributs href et src.
        $sanitizedHtml = preg_replace('/\s(href|src)\s*=\s*("|\')\s*javascript:[^\2]*\2/i', ' $1="#"', $sanitizedHtml) ?? $sanitizedHtml;

        // Retourne un HTML nettoye et trimme.
        return trim($sanitizedHtml);
    }
}
