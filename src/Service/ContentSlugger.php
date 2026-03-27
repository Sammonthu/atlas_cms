<?php
// Declare le namespace du service.
namespace App\Service;

// Importe l'EntityManager Doctrine.
use Doctrine\ORM\EntityManagerInterface;
// Importe l'interface slugger Symfony.
use Symfony\Component\String\Slugger\SluggerInterface;

// Declare un service pour normaliser les slugs SEO.
class ContentSlugger
{
    // Injecte le slugger Symfony.
    public function __construct(private readonly SluggerInterface $slugger)
    {
        // Aucun traitement additionnel a la construction.
    }

    // Genere un slug a partir d'une valeur libre.
    public function slugify(string $value): string
    {
        // Retourne une version SEO en minuscules.
        return mb_strtolower($this->slugger->slug($value)->toString());
    }

    // Genere un slug unique pour une entite Doctrine.
    public function createUniqueSlug(
        EntityManagerInterface $entityManager,
        string $entityClass,
        string $source,
        ?int $currentId = null
    ): string {
        // Genere le slug de base depuis la source.
        $baseSlug = $this->slugify($source !== '' ? $source : 'contenu');
        // Initialise le slug candidat avec la base.
        $candidateSlug = $baseSlug;
        // Initialise le suffixe numerique.
        $suffix = 1;

        // Boucle tant qu'un slug identique existe deja.
        while ($this->slugExists($entityManager, $entityClass, $candidateSlug, $currentId)) {
            // Incremente le suffixe numerique.
            ++$suffix;
            // Construit un nouveau candidat avec suffixe.
            $candidateSlug = sprintf('%s-%d', $baseSlug, $suffix);
        }

        // Retourne un slug unique et exploitable.
        return $candidateSlug;
    }

    // Verifie l'existence d'un slug pour une entite donnee.
    private function slugExists(
        EntityManagerInterface $entityManager,
        string $entityClass,
        string $slug,
        ?int $currentId
    ): bool {
        // Cree un query builder securise via Doctrine.
        $queryBuilder = $entityManager->createQueryBuilder();
        // Selectionne un compteur minimal pour la verification.
        $queryBuilder->select('COUNT(e.id)');
        // Declare la source entite de la requete.
        $queryBuilder->from($entityClass, 'e');
        // Filtre sur le slug recherche.
        $queryBuilder->where('e.slug = :slug');
        // Lie le parametre de slug (anti-injection SQL).
        $queryBuilder->setParameter('slug', $slug);

        // Verifie la presence d'un identifiant courant.
        if ($currentId !== null) {
            // Exclut l'entite courante pour l'edition.
            $queryBuilder->andWhere('e.id != :currentId');
            // Lie le parametre d'identifiant courant.
            $queryBuilder->setParameter('currentId', $currentId);
        }

        // Execute la requete compteur sous forme scalaire.
        $count = (int) $queryBuilder->getQuery()->getSingleScalarResult();

        // Retourne vrai si un slug est deja present.
        return $count > 0;
    }
}
