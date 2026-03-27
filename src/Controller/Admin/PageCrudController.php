<?php
// Déclare le namespace du CRUD page.
namespace App\Controller\Admin;

// Importe l'entité Page.
use App\Entity\Page;
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
// Importe l'attribut de sécurité Symfony.
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Réserve ce CRUD aux éditeurs et administrateurs.
#[IsGranted('ROLE_EDITOR')]
class PageCrudController extends AbstractCrudController
{
    // Injecte le service de génération de slug.
    public function __construct(private readonly ContentSlugger $contentSlugger)
    {
        // Ne réalise aucun traitement complémentaire.
    }

    // Retourne la classe d'entité gérée.
    public static function getEntityFqcn(): string
    {
        // Retourne la classe Page.
        return Page::class;
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
            ->setEntityLabelInSingular('Page')
            // Définit le label pluriel.
            ->setEntityLabelInPlural('Pages')
            // Active la recherche textuelle sur titre, slug et contenu.
            ->setSearchFields(['title', 'slug', 'content', 'metaDescription', 'category.name'])
            // Définit le tri par défaut.
            ->setDefaultSort(['updatedAt' => 'DESC']);
    }

    // Configure les filtres de la grille.
    public function configureFilters(Filters $filters): Filters
    {
        // Retourne les filtres utiles à la gestion.
        return $filters
            // Ajoute un filtre titre.
            ->add(TextFilter::new('title', 'Titre'))
            // Ajoute un filtre slug.
            ->add(TextFilter::new('slug', 'Slug'))
            // Ajoute un filtre catégorie.
            ->add(EntityFilter::new('category', 'Catégorie'))
            // Ajoute un filtre galerie.
            ->add(EntityFilter::new('gallery', 'Galerie'))
            // Ajoute un filtre statut de modération.
            ->add(ChoiceFilter::new('moderationStatus', 'Statut')->setChoices([
                'En attente' => 'pending',
                'Approuvé' => 'approved',
                'Rejeté' => 'rejected',
            ]))
            // Ajoute un filtre date de création.
            ->add(DateTimeFilter::new('createdAt', 'Créée le'))
            // Ajoute un filtre date de mise à jour.
            ->add(DateTimeFilter::new('updatedAt', 'Mise à jour'));
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
            // Affiche la galerie liée.
            AssociationField::new('gallery', 'Galerie')->setRequired(false)->setFormTypeOption('choice_label', 'name'),
            // Affiche la méta-description hors index.
            TextareaField::new('metaDescription', 'Méta-description')->setRequired(false)->hideOnIndex(),
            // Affiche le contenu hors index.
            TextareaField::new('content', 'Contenu')->hideOnIndex(),
            // Affiche la date de création hors formulaire.
            DateTimeField::new('createdAt', 'Créée le')->hideOnForm(),
            // Affiche la date de mise à jour hors formulaire.
            DateTimeField::new('updatedAt', 'Mise à jour')->hideOnForm(),
        ];
    }

    // Prépare l'entité avant création.
    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Vérifie le type d'entité et un slug vide.
        if ($entityInstance instanceof Page && $entityInstance->getSlug() === '') {
            // Génère automatiquement le slug depuis le titre.
            $entityInstance->setSlug($this->contentSlugger->slugify($entityInstance->getTitle()));
        }

        // Exécute la persistance standard.
        parent::persistEntity($entityManager, $entityInstance);
    }

    // Prépare l'entité avant mise à jour.
    public function updateEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Vérifie le type d'entité et un slug vide.
        if ($entityInstance instanceof Page && $entityInstance->getSlug() === '') {
            // Génère automatiquement le slug depuis le titre.
            $entityInstance->setSlug($this->contentSlugger->slugify($entityInstance->getTitle()));
        }

        // Exécute la mise à jour standard.
        parent::updateEntity($entityManager, $entityInstance);
    }
}

