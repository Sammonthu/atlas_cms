<?php
// Declare le namespace des subscribers Doctrine.
namespace App\EventSubscriber;

// Importe l'entite Article.
use App\Entity\Article;
// Importe l'entite Page.
use App\Entity\Page;
// Importe le service de slug.
use App\Service\ContentSlugger;
// Importe le service de sanitation HTML.
use App\Service\HtmlContentSanitizer;
// Importe l'interface EventSubscriber Doctrine.
use Doctrine\Common\EventSubscriber;
// Importe les events ORM.
use Doctrine\ORM\Events;
// Importe l'EntityManager ORM.
use Doctrine\ORM\EntityManagerInterface;
// Importe les arguments de cycle de vie prePersist.
use Doctrine\ORM\Event\PrePersistEventArgs;
// Importe les arguments de cycle de vie preUpdate.
use Doctrine\ORM\Event\PreUpdateEventArgs;

// Declare un subscriber Doctrine pour harmoniser SEO et securite contenu.
class ContentLifecycleSubscriber implements EventSubscriber
{
    // Injecte les services metier necessaires.
    public function __construct(
        private readonly ContentSlugger $contentSlugger,
        private readonly HtmlContentSanitizer $htmlContentSanitizer
    ) {
        // Ne realise aucun traitement additionnel.
    }

    // Retourne la liste des evenements ecoutes.
    public function getSubscribedEvents(): array
    {
        // Ecoute prePersist et preUpdate.
        return [Events::prePersist, Events::preUpdate];
    }

    // Traite les regles metier avant insertion SQL.
    public function prePersist(PrePersistEventArgs $eventArgs): void
    {
        // Recupere l'entite concernee.
        $entity = $eventArgs->getObject();
        // Recupere l'object manager courant.
        $objectManager = $eventArgs->getObjectManager();

        // Verifie que l'object manager est un EntityManager ORM.
        if (!$objectManager instanceof EntityManagerInterface) {
            // Sort sans traitement si le manager est incompatible.
            return;
        }

        // Applique les regles sur une page.
        if ($entity instanceof Page) {
            // Synchronise slug et contenu securise pour la page.
            $this->synchronizePage($entity, $objectManager);
        }

        // Applique les regles sur un article.
        if ($entity instanceof Article) {
            // Synchronise slug et contenu securise pour l'article.
            $this->synchronizeArticle($entity, $objectManager);
        }
    }

    // Traite les regles metier avant mise a jour SQL.
    public function preUpdate(PreUpdateEventArgs $eventArgs): void
    {
        // Recupere l'entite concernee.
        $entity = $eventArgs->getObject();
        // Recupere l'object manager courant.
        $objectManager = $eventArgs->getObjectManager();

        // Verifie que l'object manager est un EntityManager ORM.
        if (!$objectManager instanceof EntityManagerInterface) {
            // Sort sans traitement si le manager est incompatible.
            return;
        }

        // Applique les regles sur une page.
        if ($entity instanceof Page) {
            // Synchronise slug et contenu securise pour la page.
            $this->synchronizePage($entity, $objectManager);
        }

        // Applique les regles sur un article.
        if ($entity instanceof Article) {
            // Synchronise slug et contenu securise pour l'article.
            $this->synchronizeArticle($entity, $objectManager);
        }

        // Recalcule les changements Doctrine apres mutations preUpdate.
        $objectManager->getUnitOfWork()->recomputeSingleEntityChangeSet(
            $objectManager->getClassMetadata($entity::class),
            $entity
        );
    }

    // Synchronise les donnees SEO et contenu d'une page.
    private function synchronizePage(Page $page, EntityManagerInterface $entityManager): void
    {
        // Recupere la source de slug depuis le slug manuel ou le titre.
        $slugSource = $page->getSlug() !== '' ? $page->getSlug() : $page->getTitle();
        // Genere un slug unique pour la page.
        $page->setSlug($this->contentSlugger->createUniqueSlug($entityManager, Page::class, $slugSource, $page->getId()));
        // Nettoie le contenu HTML de la page contre XSS.
        $page->setContent($this->htmlContentSanitizer->sanitizeRichText($page->getContent()));
        // Complete automatiquement la meta-description si vide.
        if (($page->getMetaDescription() ?? '') === '') {
            // Cree un resume SEO court a partir du texte brut.
            $page->setMetaDescription($this->buildMetaDescription($page->getContent()));
        }
    }

    // Synchronise les donnees SEO et contenu d'un article.
    private function synchronizeArticle(Article $article, EntityManagerInterface $entityManager): void
    {
        // Recupere la source de slug depuis le slug manuel ou le titre.
        $slugSource = $article->getSlug() !== '' ? $article->getSlug() : $article->getTitle();
        // Genere un slug unique pour l'article.
        $article->setSlug($this->contentSlugger->createUniqueSlug($entityManager, Article::class, $slugSource, $article->getId()));
        // Nettoie le contenu HTML de l'article contre XSS.
        $article->setContent($this->htmlContentSanitizer->sanitizeRichText($article->getContent()));
        // Complete automatiquement la meta-description si vide.
        if (($article->getMetaDescription() ?? '') === '') {
            // Cree un resume SEO court a partir du texte brut.
            $article->setMetaDescription($this->buildMetaDescription($article->getContent()));
        }
    }

    // Construit une meta-description concise depuis un contenu HTML.
    private function buildMetaDescription(string $content): string
    {
        // Supprime toutes les balises HTML pour obtenir du texte brut.
        $plainText = trim(strip_tags($content));
        // Retourne un texte tronque a 155 caracteres pour SEO.
        return mb_substr($plainText, 0, 155);
    }
}
