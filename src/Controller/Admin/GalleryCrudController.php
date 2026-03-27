<?php
// Déclare le namespace du CRUD galerie.
namespace App\Controller\Admin;

// Importe l'entité Gallery.
use App\Entity\Gallery;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
// Importe les filtres EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
// Importe l'attribut de sécurité Symfony.
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Réserve ce CRUD aux éditeurs et administrateurs.
#[IsGranted('ROLE_EDITOR')]
class GalleryCrudController extends AbstractCrudController
{
    // Retourne la classe d'entité gérée.
    public static function getEntityFqcn(): string
    {
        // Retourne la classe Gallery.
        return Gallery::class;
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
            ->setEntityLabelInSingular('Galerie')
            // Définit le label pluriel.
            ->setEntityLabelInPlural('Galeries')
            // Active la recherche sur nom et description.
            ->setSearchFields(['name', 'description'])
            // Définit le tri par défaut.
            ->setDefaultSort(['updatedAt' => 'DESC']);
    }

    // Configure les filtres de la grille.
    public function configureFilters(Filters $filters): Filters
    {
        // Retourne les filtres utiles.
        return $filters
            // Ajoute un filtre nom.
            ->add(TextFilter::new('name', 'Nom'))
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
            // Affiche l'id uniquement en index.
            IdField::new('id')->onlyOnIndex(),
            // Affiche le nom de la galerie.
            TextField::new('name', 'Nom'),
            // Affiche le nombre d'images liées.
            IntegerField::new('images', 'Nb images')
                ->formatValue(static fn ($value, Gallery $entity): int => $entity->getImages()->count())
                ->onlyOnIndex(),
            // Affiche la description hors index.
            TextareaField::new('description', 'Description')->setRequired(false)->hideOnIndex(),
            // Affiche la date de création hors formulaire.
            DateTimeField::new('createdAt', 'Créée le')->hideOnForm(),
            // Affiche la date de mise à jour hors formulaire.
            DateTimeField::new('updatedAt', 'Mise à jour')->hideOnForm(),
        ];
    }
}

