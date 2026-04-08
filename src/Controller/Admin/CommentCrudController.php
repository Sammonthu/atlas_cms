<?php
// Declare le namespace du CRUD commentaire.
namespace App\Controller\Admin;

// Importe l'entite Comment.
use App\Entity\Comment;
// Importe l'EntityManager Doctrine.
use Doctrine\ORM\EntityManagerInterface;
// Importe la configuration d'actions EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
// Importe la configuration CRUD EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
// Importe la configuration des filtres EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
// Importe le contexte EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
// Importe le controleur CRUD abstrait.
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
// Importe le generateur d'URL admin.
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
// Importe les champs EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
// Importe les filtres EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
// Importe l'attribut de securite Symfony.
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Reserve ce CRUD aux editeurs et administrateurs.
#[IsGranted('ROLE_EDITOR')]
class CommentCrudController extends AbstractCrudController
{
    // Injecte l'EntityManager et le generateur d'URL admin.
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {
        // Ne realise aucun traitement complementaire.
    }

    // Retourne la classe d'entite geree.
    public static function getEntityFqcn(): string
    {
        // Retourne la classe Comment.
        return Comment::class;
    }

    // Configure les actions disponibles.
    public function configureActions(Actions $actions): Actions
    {
        // Construit l'action rapide d'activation.
        $activateAction = Action::new('approveComment', 'Activer', 'fa fa-check')
            // Affiche l'action uniquement en index.
            ->displayIf(static fn (Comment $comment): bool => $comment->getStatus() !== Comment::STATUS_APPROVED)
            // Lien vers l'action CRUD interne.
            ->linkToCrudAction('approveComment')
            // Applique un style succes.
            ->addCssClass('btn btn-sm btn-success');

        // Construit l'action rapide de desactivation.
        $deactivateAction = Action::new('rejectComment', 'Desactiver', 'fa fa-ban')
            // Affiche l'action uniquement en index.
            ->displayIf(static fn (Comment $comment): bool => $comment->getStatus() !== Comment::STATUS_REJECTED)
            // Lien vers l'action CRUD interne.
            ->linkToCrudAction('rejectComment')
            // Applique un style danger.
            ->addCssClass('btn btn-sm btn-danger');

        // Retourne les permissions par action.
        return $actions
            // Autorise l'index aux editeurs.
            ->setPermission(Action::INDEX, 'ROLE_EDITOR')
            // Autorise la creation aux editeurs.
            ->setPermission(Action::NEW, 'ROLE_EDITOR')
            // Autorise l'edition aux editeurs.
            ->setPermission(Action::EDIT, 'ROLE_EDITOR')
            // Reserve la suppression aux admins.
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            // Ajoute l'action activer en index.
            ->add(Crud::PAGE_INDEX, $activateAction)
            // Ajoute l'action desactiver en index.
            ->add(Crud::PAGE_INDEX, $deactivateAction);
    }

    // Configure les options globales du CRUD.
    public function configureCrud(Crud $crud): Crud
    {
        // Retourne la configuration de lisibilite.
        return $crud
            // Definit le label singulier.
            ->setEntityLabelInSingular('Commentaire')
            // Definit le label pluriel.
            ->setEntityLabelInPlural('Commentaires')
            // Active la recherche multi-champs.
            ->setSearchFields(['content', 'status', 'article.title', 'author.email'])
            // Definit le tri par defaut.
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    // Configure les filtres de la grille.
    public function configureFilters(Filters $filters): Filters
    {
        // Retourne les filtres utiles a la moderation.
        return $filters
            // Ajoute un filtre contenu.
            ->add(TextFilter::new('content', 'Contenu'))
            // Ajoute un filtre statut.
            ->add(ChoiceFilter::new('status', 'Statut')->setChoices([
                'En attente' => Comment::STATUS_PENDING,
                'Actif' => Comment::STATUS_APPROVED,
                'Desactive' => Comment::STATUS_REJECTED,
            ]))
            // Ajoute un filtre article.
            ->add(EntityFilter::new('article', 'Article'))
            // Ajoute un filtre auteur.
            ->add(EntityFilter::new('author', 'Auteur'))
            // Ajoute un filtre date.
            ->add(DateTimeFilter::new('createdAt', 'Cree le'));
    }

    // Configure les champs affiches selon le contexte.
    public function configureFields(string $pageName): iterable
    {
        // Retourne la liste des champs.
        return [
            // Affiche l'id uniquement en index.
            IdField::new('id')->onlyOnIndex(),
            // Affiche le statut en badges colores.
            ChoiceField::new('status', 'Statut')
                ->setChoices([
                    'En attente' => Comment::STATUS_PENDING,
                    'Actif' => Comment::STATUS_APPROVED,
                    'Desactive' => Comment::STATUS_REJECTED,
                ])
                ->renderAsBadges([
                    Comment::STATUS_PENDING => 'warning',
                    Comment::STATUS_APPROVED => 'success',
                    Comment::STATUS_REJECTED => 'danger',
                ]),
            // Affiche l'article lie.
            AssociationField::new('article', 'Article')->setFormTypeOption('choice_label', 'title'),
            // Affiche l'auteur lie.
            AssociationField::new('author', 'Auteur')->setFormTypeOption('choice_label', 'email'),
            // Affiche le contenu complet hors index.
            TextareaField::new('content', 'Contenu')->hideOnIndex(),
            // Affiche un apercu contenu en index.
            TextareaField::new('content', 'Apercu')
                ->formatValue(static function ($value): string {
                    // Convertit la valeur en chaine.
                    $text = (string) $value;
                    // Retourne un extrait court.
                    return mb_strimwidth($text, 0, 90, '...');
                })
                ->onlyOnIndex(),
            // Affiche la date de creation hors formulaire.
            DateTimeField::new('createdAt', 'Cree le')->hideOnForm(),
        ];
    }

    // Action d'activation rapide depuis EasyAdmin.
    public function approveComment(AdminContext $context): \Symfony\Component\HttpFoundation\Response
    {
        // Recupere l'entite courante du contexte admin.
        $comment = $context->getEntity()->getInstance();

        // Verifie le type d'entite attendu.
        if ($comment instanceof Comment) {
            // Definit le statut actif.
            $comment->setStatus(Comment::STATUS_APPROVED);
            // Execute la transaction SQL.
            $this->entityManager->flush();
            // Affiche un flash de confirmation.
            $this->addFlash('success', 'Commentaire active.');
        }

        // Redirige vers la liste des commentaires.
        return $this->redirect((clone $this->adminUrlGenerator)
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }

    // Action de desactivation rapide depuis EasyAdmin.
    public function rejectComment(AdminContext $context): \Symfony\Component\HttpFoundation\Response
    {
        // Recupere l'entite courante du contexte admin.
        $comment = $context->getEntity()->getInstance();

        // Verifie le type d'entite attendu.
        if ($comment instanceof Comment) {
            // Definit le statut desactive.
            $comment->setStatus(Comment::STATUS_REJECTED);
            // Execute la transaction SQL.
            $this->entityManager->flush();
            // Affiche un flash de confirmation.
            $this->addFlash('success', 'Commentaire desactive.');
        }

        // Redirige vers la liste des commentaires.
        return $this->redirect((clone $this->adminUrlGenerator)
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl());
    }
}