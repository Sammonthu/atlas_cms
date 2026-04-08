<?php
// Declare le namespace du CRUD article.
namespace App\Controller\Admin;

// Importe l'entite Article.
use App\Entity\Article;
// Importe l'entite User.
use App\Entity\User;
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
// Importe le helper securite Symfony.
use Symfony\Bundle\SecurityBundle\Security;
// Importe l'attribut de securite Symfony.
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Reserve ce CRUD aux editeurs et administrateurs.
#[IsGranted('ROLE_EDITOR')]
class ArticleCrudController extends AbstractCrudController
{
    // Injecte les dependances utiles.
    public function __construct(
        private readonly ContentSlugger $contentSlugger,
        private readonly Security $security
    ) {
        // Ne realise aucun traitement complementaire.
    }

    // Retourne la classe d'entite geree.
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
            ->setEntityLabelInSingular('Article')
            // Definit le label pluriel.
            ->setEntityLabelInPlural('Articles')
            // Active la recherche multi-champs.
            ->setSearchFields(['title', 'slug', 'content', 'metaDescription', 'category.name', 'author.email', 'tags.name'])
            // Definit le tri par defaut.
            ->setDefaultSort(['publishedAt' => 'DESC', 'createdAt' => 'DESC']);
    }

    // Configure les filtres de la grille.
    public function configureFilters(Filters $filters): Filters
    {
        // Retourne les filtres utiles a la moderation.
        return $filters
            // Ajoute un filtre titre.
            ->add(TextFilter::new('title', 'Titre'))
            // Ajoute un filtre slug.
            ->add(TextFilter::new('slug', 'Slug'))
            // Ajoute un filtre categorie.
            ->add(EntityFilter::new('category', 'Categorie'))
            // Ajoute un filtre auteur.
            ->add(EntityFilter::new('author', 'Auteur'))
            // Ajoute un filtre tags.
            ->add(EntityFilter::new('tags', 'Tags'))
            // Ajoute un filtre statut de moderation.
            ->add(ChoiceFilter::new('moderationStatus', 'Statut')->setChoices([
                'En attente' => 'pending',
                'Approuve' => 'approved',
                'Rejete' => 'rejected',
            ]))
            // Ajoute un filtre date de publication.
            ->add(DateTimeFilter::new('publishedAt', 'Publie le'));
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
            // Affiche la categorie liee.
            AssociationField::new('category', 'Categorie')->setFormTypeOption('choice_label', 'name'),
            // Affiche l'auteur lie.
            AssociationField::new('author', 'Auteur')->setFormTypeOption('choice_label', 'email'),
            // Affiche la relation tags.
            AssociationField::new('tags', 'Tags')->setFormTypeOption('choice_label', 'name'),
            // Affiche la date de publication.
            DateTimeField::new('publishedAt', 'Publie le')->setRequired(false),
            // Affiche la meta-description hors index.
            TextareaField::new('metaDescription', 'Meta-description')->setRequired(false)->hideOnIndex(),
            // Active un editeur WYSIWYG pour le contenu hors index.
            TextEditorField::new('content', 'Contenu')->hideOnIndex(),
            // Affiche la date de creation hors formulaire.
            DateTimeField::new('createdAt', 'Cree le')->hideOnForm(),
            // Affiche la date de mise a jour hors formulaire.
            DateTimeField::new('updatedAt', 'Mis a jour le')->hideOnForm(),
        ];
    }

    // Prepare l'entite avant creation.
    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Verifie le type d'entite article.
        if ($entityInstance instanceof Article) {
            // Genere le slug automatiquement si vide.
            if ($entityInstance->getSlug() === '') {
                // Construit le slug depuis le titre.
                $entityInstance->setSlug($this->contentSlugger->slugify($entityInstance->getTitle()));
            }

            // Definit l'auteur connecte si absent.
            if ($entityInstance->getAuthor() === null) {
                // Recupere l'utilisateur connecte.
                $user = $this->security->getUser();
                // Verifie le type utilisateur.
                if ($user instanceof User) {
                    // Affecte l'auteur connecte.
                    $entityInstance->setAuthor($user);
                }
            }
        }

        // Execute la persistance standard.
        parent::persistEntity($entityManager, $entityInstance);
    }

    // Prepare l'entite avant mise a jour.
    public function updateEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Verifie le type d'entite et un slug vide.
        if ($entityInstance instanceof Article && $entityInstance->getSlug() === '') {
            // Genere automatiquement le slug depuis le titre.
            $entityInstance->setSlug($this->contentSlugger->slugify($entityInstance->getTitle()));
        }

        // Execute la mise a jour standard.
        parent::updateEntity($entityManager, $entityInstance);
    }
}