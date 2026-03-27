<?php
// Déclare le namespace du CRUD utilisateur.
namespace App\Controller\Admin;

// Importe l'entité User.
use App\Entity\User;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
// Importe les filtres EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
// Importe le hasher de mot de passe.
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
// Importe l'attribut de sécurité Symfony.
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Réserve le CRUD utilisateur aux administrateurs.
#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    // Injecte le service de hashage des mots de passe.
    public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
    {
        // Ne réalise aucun traitement complémentaire.
    }

    // Retourne la classe d'entité gérée.
    public static function getEntityFqcn(): string
    {
        // Retourne la classe User.
        return User::class;
    }

    // Configure les actions disponibles.
    public function configureActions(Actions $actions): Actions
    {
        // Retourne les permissions par action.
        return $actions
            // Autorise l'index aux admins.
            ->setPermission(Action::INDEX, 'ROLE_ADMIN')
            // Autorise la création aux admins.
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            // Autorise l'édition aux admins.
            ->setPermission(Action::EDIT, 'ROLE_ADMIN')
            // Autorise la suppression aux admins.
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    // Configure les options globales du CRUD.
    public function configureCrud(Crud $crud): Crud
    {
        // Retourne la configuration de lisibilité.
        return $crud
            // Définit le label singulier.
            ->setEntityLabelInSingular('Utilisateur')
            // Définit le label pluriel.
            ->setEntityLabelInPlural('Utilisateurs')
            // Active la recherche sur nom et email.
            ->setSearchFields(['name', 'email'])
            // Définit le tri par défaut.
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    // Configure les filtres de la grille.
    public function configureFilters(Filters $filters): Filters
    {
        // Retourne les filtres utiles en démonstration.
        return $filters
            // Ajoute un filtre texte sur le nom.
            ->add(TextFilter::new('name', 'Nom'))
            // Ajoute un filtre texte sur l'email.
            ->add(TextFilter::new('email', 'Email'))
            // Ajoute un filtre sur date de création.
            ->add(DateTimeFilter::new('createdAt', 'Créé le'));
    }

    // Configure les champs affichés selon le contexte.
    public function configureFields(string $pageName): iterable
    {
        // Retourne la liste des champs.
        return [
            // Affiche l'id uniquement en index.
            IdField::new('id')->onlyOnIndex(),
            // Affiche le nom.
            TextField::new('name', 'Nom'),
            // Affiche l'email.
            EmailField::new('email', 'Email'),
            // Affiche les rôles en badges en index.
            ChoiceField::new('roles', 'Rôles')
                ->setChoices([
                    'ROLE_ADMIN' => 'ROLE_ADMIN',
                    'ROLE_EDITOR' => 'ROLE_EDITOR',
                    'ROLE_USER' => 'ROLE_USER',
                ])
                ->allowMultipleChoices()
                ->renderAsBadges([
                    'ROLE_ADMIN' => 'danger',
                    'ROLE_EDITOR' => 'info',
                    'ROLE_USER' => 'secondary',
                ])
                ->onlyOnIndex(),
            // Affiche les rôles en édition sur formulaire.
            ChoiceField::new('roles', 'Rôles')
                ->setChoices([
                    'Administrateur' => 'ROLE_ADMIN',
                    'Rédacteur' => 'ROLE_EDITOR',
                    'Utilisateur' => 'ROLE_USER',
                ])
                ->allowMultipleChoices()
                ->renderExpanded(false)
                ->onlyOnForms(),
            // Affiche la date de création hors formulaire.
            DateTimeField::new('createdAt', 'Créé le')->hideOnForm(),
            // Affiche la date de mise à jour hors formulaire.
            DateTimeField::new('updatedAt', 'Mis à jour le')->hideOnForm(),
            // Affiche le mot de passe en clair sur formulaire.
            TextField::new('password', 'Mot de passe (en clair)')
                ->setHelp('En modification, laissez vide pour conserver le mot de passe actuel.')
                ->setRequired($pageName === Crud::PAGE_NEW)
                ->setFormTypeOption('empty_data', '')
                ->onlyOnForms(),
        ];
    }

    // Hash le mot de passe avant création.
    public function persistEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Vérifie le type d'entité.
        if ($entityInstance instanceof User) {
            // Hash le mot de passe saisi.
            $entityInstance->setPassword($this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPassword()));
        }

        // Exécute la persistance standard.
        parent::persistEntity($entityManager, $entityInstance);
    }

    // Hash le mot de passe avant mise à jour.
    public function updateEntity(\Doctrine\ORM\EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Vérifie le type d'entité.
        if ($entityInstance instanceof User) {
            // Récupère l'état original Doctrine.
            $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
            // Récupère le hash original.
            $originalPassword = (string) ($originalData['password'] ?? '');
            // Récupère la valeur soumise.
            $submittedPassword = $entityInstance->getPassword();
            // Vérifie si le mot de passe soumis est vide.
            if ($submittedPassword === '') {
                // Conserve le hash existant.
                $entityInstance->setPassword($originalPassword);
            } elseif ($submittedPassword !== $originalPassword) {
                // Hash le nouveau mot de passe.
                $entityInstance->setPassword($this->passwordHasher->hashPassword($entityInstance, $submittedPassword));
            }
        }

        // Exécute la mise à jour standard.
        parent::updateEntity($entityManager, $entityInstance);
    }
}

