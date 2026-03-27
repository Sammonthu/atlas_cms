<?php
// Déclare le namespace du CRUD tag.
namespace App\Controller\Admin;

// Importe l'entité Tag.
use App\Entity\Tag;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
// Importe les filtres EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
// Importe l'attribut de sécurité Symfony.
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Réserve ce CRUD aux administrateurs.
#[IsGranted('ROLE_ADMIN')]
class TagCrudController extends AbstractCrudController
{
    // Retourne la classe d'entité gérée.
    public static function getEntityFqcn(): string
    {
        // Retourne la classe Tag.
        return Tag::class;
    }

    // Configure les actions disponibles.
    public function configureActions(Actions $actions): Actions
    {
        // Retourne les permissions par action.
        return $actions
            // Autorise toutes les actions aux admins.
            ->setPermission(Action::INDEX, 'ROLE_ADMIN')
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    // Configure les options globales du CRUD.
    public function configureCrud(Crud $crud): Crud
    {
        // Retourne la configuration de lisibilité.
        return $crud
            // Définit le label singulier.
            ->setEntityLabelInSingular('Tag')
            // Définit le label pluriel.
            ->setEntityLabelInPlural('Tags')
            // Active la recherche sur le nom.
            ->setSearchFields(['name'])
            // Définit le tri alphabétique.
            ->setDefaultSort(['name' => 'ASC']);
    }

    // Configure les filtres de la grille.
    public function configureFilters(Filters $filters): Filters
    {
        // Retourne les filtres disponibles.
        return $filters
            // Ajoute un filtre texte sur le nom.
            ->add(TextFilter::new('name', 'Nom'));
    }

    // Configure les champs affichés.
    public function configureFields(string $pageName): iterable
    {
        // Retourne la liste des champs.
        return [
            // Affiche l'id uniquement en index.
            IdField::new('id')->onlyOnIndex(),
            // Affiche le nom du tag.
            TextField::new('name', 'Nom'),
            // Affiche le nombre d'articles liés.
            IntegerField::new('articles', 'Articles liés')
                ->formatValue(static fn ($value, Tag $entity): int => $entity->getArticles()->count())
                ->onlyOnIndex(),
        ];
    }
}

