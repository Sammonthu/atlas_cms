<?php
// Déclare le namespace du CRUD image.
namespace App\Controller\Admin;

// Importe l'entité Image.
use App\Entity\Image;
// Importe le service d'upload image.
use App\Service\ImageUploadService;
// Importe l'EntityManager Doctrine.
use Doctrine\ORM\EntityManagerInterface;
// Importe la configuration d'actions EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
// Importe la configuration CRUD EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
// Importe la configuration des filtres EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
// Importe le contrôleur CRUD abstrait.
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
// Importe le champ générique EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
// Importe les champs EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
// Importe les filtres EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
// Importe le type de formulaire fichier Symfony.
use Symfony\Component\Form\Extension\Core\Type\FileType;
// Importe l'attribut de sécurité Symfony.
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Réserve ce CRUD aux éditeurs et administrateurs.
#[IsGranted('ROLE_EDITOR')]
class ImageCrudController extends AbstractCrudController
{
    // Injecte le service d'upload sécurisé.
    public function __construct(private readonly ImageUploadService $imageUploadService)
    {
        // Ne réalise aucun traitement complémentaire.
    }

    // Retourne la classe d'entité gérée.
    public static function getEntityFqcn(): string
    {
        // Retourne la classe Image.
        return Image::class;
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
            ->setEntityLabelInSingular('Image')
            // Définit le label pluriel.
            ->setEntityLabelInPlural('Images')
            // Active la recherche sur chemin et légende.
            ->setSearchFields(['filePath', 'caption', 'gallery.name'])
            // Définit le tri par défaut.
            ->setDefaultSort(['uploadedAt' => 'DESC']);
    }

    // Configure les filtres de la grille.
    public function configureFilters(Filters $filters): Filters
    {
        // Retourne les filtres utiles.
        return $filters
            // Ajoute un filtre chemin.
            ->add(TextFilter::new('filePath', 'Chemin'))
            // Ajoute un filtre légende.
            ->add(TextFilter::new('caption', 'Légende'))
            // Ajoute un filtre galerie.
            ->add(EntityFilter::new('gallery', 'Galerie'))
            // Ajoute un filtre date d'upload.
            ->add(DateTimeFilter::new('uploadedAt', 'Téléversée le'));
    }

    // Configure les champs affichés selon le contexte.
    public function configureFields(string $pageName): iterable
    {
        // Retourne la liste des champs.
        return [
            // Affiche l'id uniquement en index.
            IdField::new('id')->onlyOnIndex(),
            // Affiche une miniature en index.
            ImageField::new('filePath', 'Miniature')
                ->setBasePath('/')
                ->onlyOnIndex(),
            // Affiche le champ upload en formulaire.
            Field::new('imageFile', 'Fichier image')
                ->setFormType(FileType::class)
                ->setFormTypeOption('required', $pageName === Crud::PAGE_NEW)
                ->setFormTypeOption('attr', ['accept' => 'image/jpeg,image/png,image/webp,image/gif'])
                ->setHelp('Formats autorisés : JPG, PNG, WEBP, GIF. Taille max : 5 Mo.')
                ->onlyOnForms(),
            // Affiche le chemin fichier hors formulaire.
            TextField::new('filePath', 'Chemin stocké')->hideOnForm(),
            // Affiche la légende.
            TextField::new('caption', 'Légende')->setRequired(false),
            // Affiche la galerie liée.
            AssociationField::new('gallery', 'Galerie')->setFormTypeOption('choice_label', 'name'),
            // Affiche la date de téléversement hors formulaire.
            DateTimeField::new('uploadedAt', 'Téléversée le')->hideOnForm(),
        ];
    }

    // Gère la persistance avec upload sécurisé.
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Vérifie le type de l'entité.
        if (!$entityInstance instanceof Image) {
            // Délègue la persistance standard.
            parent::persistEntity($entityManager, $entityInstance);
            // Quitte la méthode.
            return;
        }

        // Récupère le fichier uploadé.
        $uploadedFile = $entityInstance->getImageFile();
        // Vérifie qu'un fichier est fourni.
        if ($uploadedFile === null) {
            // Lève une erreur claire pour éviter une image vide.
            throw new \RuntimeException('Aucun fichier image fourni pour le téléversement.');
        }

        // Téléverse le fichier de manière sécurisée.
        $publicPath = $this->imageUploadService->uploadGalleryImage($uploadedFile);
        // Stocke le chemin public en base.
        $entityInstance->setFilePath($publicPath);
        // Met à jour la date de téléversement.
        $entityInstance->setUploadedAt(new \DateTimeImmutable());

        // Délègue la persistance standard.
        parent::persistEntity($entityManager, $entityInstance);
    }

    // Gère la mise à jour avec remplacement optionnel.
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Vérifie le type de l'entité.
        if (!$entityInstance instanceof Image) {
            // Délègue la mise à jour standard.
            parent::updateEntity($entityManager, $entityInstance);
            // Quitte la méthode.
            return;
        }

        // Récupère les données originales Doctrine.
        $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
        // Récupère l'ancien chemin de fichier.
        $oldPath = (string) ($originalData['filePath'] ?? '');
        // Récupère le nouveau fichier uploadé.
        $uploadedFile = $entityInstance->getImageFile();

        // Vérifie si un nouveau fichier est fourni.
        if ($uploadedFile !== null) {
            // Téléverse le nouveau fichier de manière sécurisée.
            $newPath = $this->imageUploadService->uploadGalleryImage($uploadedFile);
            // Affecte le nouveau chemin public.
            $entityInstance->setFilePath($newPath);
            // Met à jour la date de téléversement.
            $entityInstance->setUploadedAt(new \DateTimeImmutable());
            // Supprime l'ancien fichier si nécessaire.
            $this->imageUploadService->removeGalleryImage($oldPath);
        }

        // Délègue la mise à jour standard.
        parent::updateEntity($entityManager, $entityInstance);
    }

    // Gère la suppression avec nettoyage du fichier physique.
    public function deleteEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        // Vérifie le type de l'entité.
        if ($entityInstance instanceof Image) {
            // Supprime le fichier associé du disque.
            $this->imageUploadService->removeGalleryImage($entityInstance->getFilePath());
        }

        // Délègue la suppression standard.
        parent::deleteEntity($entityManager, $entityInstance);
    }
}

