<?php
// Declare le namespace du CRUD page.
namespace App\Controller\Admin;

// Importe l'entite Page.
use App\Entity\Page;
// Importe le service de generation de slug.
use App\Service\ContentSlugger;
// Importe la configuration d'actions EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
// Importe la configuration CRUD EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
// Importe la configuration des filtres EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
// Importe le controleur CRUD abstrait.
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
// Importe les champs EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
// Importe les filtres EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
// Importe l'attribut de securite Symfony.
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Reserve ce CRUD aux editeurs et administrateurs.
#[IsGranted('ROLE_EDITOR')]
class PageCrudController extends AbstractCrudController
{
    // Injecte le service de generation de slug.
    public function __construct(private readonly ContentSlugger $contentSlugger)
    {
        // Ne realise aucun traitement complementaire.
    }

    // Retourne la classe d'entite geree.
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
            // Autorise l'index aux editeurs.
            ->setPermission(Action::INDEX, 'ROLE_EDITOR')
            // Autorise la creation aux editeurs.
            ->setPermission(Action::NEW, 'ROLE_EDITOR')
            // Autorise l'edition aux editeurs.
            ->setPermission(Action::EDIT, 'ROLE_EDITOR')
            // Reserve la suppression aux admins.
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    // Configure les options globales du CRUD.
    public function configureCrud(Crud $crud): Crud
    {
        // Retourne la configuration de lisibilite.
        return $crud
            // Definit le label singulier.
            ->setEntityLabelInSingular('Page')
            // Definit le label pluriel.
            ->setEntityLabelInPlural('Pages')
            // Active la recherche textuelle sur titre, slug et contenu.
            ->setSearchFields(['title', 'slug', 'content', 'metaDescription', 'category.name', 'parent.title'])
            // Definit le tri par defaut avec arborescence.
            ->setDefaultSort(['parent' => 'ASC', 'title' => 'ASC', 'updatedAt' => 'DESC']);
    }

    // Configure les filtres de la grille.
    public function configureFilters(Filters $filters): Filters
    {
        // Retourne les filtres utiles a la gestion.
        return $filters
            // Ajoute un filtre titre.
            ->add(TextFilter::new('title', 'Titre'))
            // Ajoute un filtre slug.
            ->add(TextFilter::new('slug', 'Slug'))
            // Ajoute un filtre parent pour l'arborescence.
            ->add(EntityFilter::new('parent', 'Page parente'))
            // Ajoute un filtre categorie.
            ->add(EntityFilter::new('category', 'Categorie'))
            // Ajoute un filtre galerie.
            ->add(EntityFilter::new('gallery', 'Galerie'))
            // Ajoute un filtre statut de moderation.
            ->add(ChoiceFilter::new('moderationStatus', 'Statut')->setChoices([
                'En attente' => 'pending',
                'Approuve' => 'approved',
                'Rejete' => 'rejected',
            ]))
            // Ajoute un filtre date de creation.
            ->add(DateTimeFilter::new('createdAt', 'Creee le'))
            // Ajoute un filtre date de mise a jour.
            ->add(DateTimeFilter::new('updatedAt', 'Mise a jour'));
    }

    // Configure les champs affiches selon le contexte.
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
            // Affiche le statut en badges colores.
            ChoiceField::new('moderationStatus', 'Statut')
                ->setChoices([
                    'En attente' => 'pending',
                    'Approuve' => 'approved',
                    'Rejete' => 'rejected',
                ])
                ->renderAsBadges([
                    'pending' => 'warning',
                    'approved' => 'success',
                    'rejected' => 'danger',
                ]),
            // Affiche la page parente pour l'arborescence.
            AssociationField::new('parent', 'Page parente')
                ->setRequired(false)
                ->setFormTypeOption('choice_label', 'title')
                ->setFormTypeOption('placeholder', 'Aucune (racine)'),
            // Affiche la categorie liee.
            AssociationField::new('category', 'Categorie')->setFormTypeOption('choice_label', 'name'),
            // Affiche la galerie liee.
            AssociationField::new('gallery', 'Galerie')->setRequired(false)->setFormTypeOption('choice_label', 'name'),
            // Affiche la meta-description hors index.
            TextareaField::new('metaDescription', 'Meta-description')->setRequired(false)->hideOnIndex(),
            // Active un editeur WYSIWYG pour le contenu hors index.
            TextEditorField::new('content', 'Contenu')->hideOnIndex(),
            // Affiche la date de creation hors formulaire.
            DateTimeField::new('createdAt', 'Creee le')->hideOnForm(),
            // Affiche la date de mise a jour hors formulaire.
            DateTimeField::new('updatedAt', 'Mise a jour')->hideOnForm(),
        ];
    }

    // Prepare l'entite avant creation.
    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Normalise slug et arborescence avant persistence.
        $this->normalizePageBeforeWrite($entityInstance);

        // Execute la persistance standard.
        parent::persistEntity($entityManager, $entityInstance);
    }

    // Prepare l'entite avant mise a jour.
    public function updateEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Normalise slug et arborescence avant mise a jour.
        $this->normalizePageBeforeWrite($entityInstance);

        // Execute la mise a jour standard.
        parent::updateEntity($entityManager, $entityInstance);
    }

    // Normalise les donnees critiques d'une page avant ecriture.
    private function normalizePageBeforeWrite(mixed $entityInstance): void
    {
        // Verifie le type d'entite page.
        if (!$entityInstance instanceof Page) {
            // Sort si l'entite n'est pas une page.
            return;
        }

        // Genere automatiquement le slug quand il est vide.
        if ($entityInstance->getSlug() === '') {
            // Construit le slug a partir du titre.
            $entityInstance->setSlug($this->contentSlugger->slugify($entityInstance->getTitle()));
        }

        // Evite qu'une page soit son propre parent.
        if ($entityInstance->getParent() === $entityInstance) {
            // Force le parent a null pour garder une arborescence valide.
            $entityInstance->setParent(null);
        }
    }
}