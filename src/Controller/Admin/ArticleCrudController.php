<?php
// Déclare le namespace du CRUD article.
namespace App\Controller\Admin;

// Importe l'entité Article.
use App\Entity\Article;
// Importe l'entité User.
use App\Entity\User;
// Importe le service de génération de slug.
use App\Service\ContentSlugger;
// Importe la configuration d'actions EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
// Importe la configuration CRUD EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
// Importe la configuration des filtres EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
// Importe le contrôleur CRUD abstrait.
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
// Importe les champs EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
// Importe les filtres EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
// Importe le helper sécurité Symfony.
use Symfony\Bundle\SecurityBundle\Security;
// Importe l'attribut de sécurité Symfony.
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Réserve ce CRUD aux éditeurs et administrateurs.
#[IsGranted('ROLE_EDITOR')]
class ArticleCrudController extends AbstractCrudController
{
    // Injecte les dépendances utiles.
    public function __construct(
        private readonly ContentSlugger $contentSlugger,
        private readonly Security $security
    ) {
        // Ne réalise aucun traitement complémentaire.
    }

    // Retourne la classe d'entité gérée.
    public static function getEntityFqcn(): string
    {
        // Retourne la classe Article.
        return Article::class;
    }

    // Configure les actions disponibles.
    public function configureActions(Actions $actions): Actions
    {
        // Retourne les permissions par action.
        return $actions
            // Autorise l'index aux éditeurs.
            ->setPermission(Action::INDEX, 'ROLE_EDITOR')
            // Autorise la création aux éditeurs.
            ->setPermission(Action::NEW, 'ROLE_EDITOR')
            // Autorise l'édition aux éditeurs.
            ->setPermission(Action::EDIT, 'ROLE_EDITOR')
            // Réserve la suppression aux admins.
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    // Configure les options globales du CRUD.
    public function configureCrud(Crud $crud): Crud
    {
        // Retourne la configuration de lisibilité.
        return $crud
            // Définit le label singulier.
            ->setEntityLabelInSingular('Article')
            // Définit le label pluriel.
            ->setEntityLabelInPlural('Articles')
            // Active la recherche multi-champs.
            ->setSearchFields(['title', 'slug', 'content', 'metaDescription', 'category.name', 'author.email', 'tags.name'])
            // Définit le tri par défaut.
            ->setDefaultSort(['publishedAt' => 'DESC', 'createdAt' => 'DESC']);
    }

    // Configure les filtres de la grille.
    public function configureFilters(Filters $filters): Filters
    {
        // Retourne les filtres utiles à la modération.
        return $filters
            // Ajoute un filtre titre.
            ->add(TextFilter::new('title', 'Titre'))
            // Ajoute un filtre slug.
            ->add(TextFilter::new('slug', 'Slug'))
            // Ajoute un filtre catégorie.
            ->add(EntityFilter::new('category', 'Catégorie'))
            // Ajoute un filtre auteur.
            ->add(EntityFilter::new('author', 'Auteur'))
            // Ajoute un filtre tags.
            ->add(EntityFilter::new('tags', 'Tags'))
            // Ajoute un filtre statut de modération.
            ->add(ChoiceFilter::new('moderationStatus', 'Statut')->setChoices([
                'En attente' => 'pending',
                'Approuvé' => 'approved',
                'Rejeté' => 'rejected',
            ]))
            // Ajoute un filtre date de publication.
            ->add(DateTimeFilter::new('publishedAt', 'Publié le'));
    }

    // Configure les champs affichés selon le contexte.
    public function configureFields(string $pageName): iterable
    {
        // Retourne la liste des champs.
        return [
            // Affiche l'identifiant uniquement en index.
            IdField::new('id')->onlyOnIndex(),
            // Affiche le titre.
            TextField::new('title', 'Titre'),
            // Affiche le slug.
            TextField::new('slug', 'Slug')->setRequired(false),
            // Affiche le statut en badges colorés.
            ChoiceField::new('moderationStatus', 'Statut')
                ->setChoices([
                    'En attente' => 'pending',
                    'Approuvé' => 'approved',
                    'Rejeté' => 'rejected',
                ])
                ->renderAsBadges([
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                ]),
            // Affiche la catégorie liée.
            AssociationField::new('category', 'Catégorie')->setFormTypeOption('choice_label', 'name'),
            // Affiche l'auteur lié.
            AssociationField::new('author', 'Auteur')->setFormTypeOption('choice_label', 'email'),
            // Affiche la relation tags.
            AssociationField::new('tags', 'Tags')->setFormTypeOption('choice_label', 'name'),
            // Affiche la date de publication.
            DateTimeField::new('publishedAt', 'Publié le')->setRequired(false),
            // Affiche la méta-description hors index.
            TextareaField::new('metaDescription', 'Méta-description')->setRequired(false)->hideOnIndex(),
            // Affiche le contenu hors index.
            TextareaField::new('content', 'Contenu')->hideOnIndex(),
            // Affiche la date de création hors formulaire.
            DateTimeField::new('createdAt', 'Créé le')->hideOnForm(),
            // Affiche la date de mise à jour hors formulaire.
            DateTimeField::new('updatedAt', 'Mis à jour le')->hideOnForm(),
        ];
    }

    // Prépare l'entité avant création.
    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Vérifie le type d'entité article.
        if ($entityInstance instanceof Article) {
            // Vérifie si le slug est vide.
            if ($entityInstance->getSlug() === '') {
                // Génère automatiquement le slug depuis le titre.
                $entityInstance->setSlug($this->contentSlugger->slugify($entityInstance->getTitle()));
            }
            // Vérifie si l'auteur est absent.
            if ($entityInstance->getAuthor() === null) {
                // Récupère l'utilisateur connecté.
                $user = $this->security->getUser();
                // Vérifie le type utilisateur.
                if ($user instanceof User) {
                    // Affecte l'auteur connecté.
                    $entityInstance->setAuthor($user);
                }
            }
        }

        // Exécute la persistance standard.
        parent::persistEntity($entityManager, $entityInstance);
    }

    // Prépare l'entité avant mise à jour.
    public function updateEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Vérifie le type d'entité et un slug vide.
        if ($entityInstance instanceof Article && $entityInstance->getSlug() === '') {
            // Génère automatiquement le slug depuis le titre.
            $entityInstance->setSlug($this->contentSlugger->slugify($entityInstance->getTitle()));
        }

        // Exécute la mise à jour standard.
        parent::updateEntity($entityManager, $entityInstance);
    }
}

