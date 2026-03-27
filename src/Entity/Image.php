<?php
// Déclare le namespace de l'entité image.
namespace App\Entity;

// Importe le mapping ORM Doctrine.
use Doctrine\ORM\Mapping as ORM;
// Importe les contraintes de validation Symfony.
use Symfony\Component\Validator\Constraints as Assert;
// Importe le type UploadedFile Symfony.
use Symfony\Component\HttpFoundation\File\UploadedFile;

// Déclare l'entité Image.
#[ORM\Entity]
// Déclare la table SQL associée.
#[ORM\Table(name: 'image')]
class Image
{
    // Déclare la clé primaire.
    #[ORM\Id]
    // Active l'auto-incrément.
    #[ORM\GeneratedValue]
    // Déclare la colonne id.
    #[ORM\Column]
    private ?int $id = null;

    // Déclare le chemin public du fichier stocké.
    #[ORM\Column(length: 255)]
    #[Assert\Length(max: 255, maxMessage: 'Le chemin ne peut pas dépasser {{ limit }} caractères.')]
    private string $filePath = '';

    // Déclare la légende de l'image.
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'La légende ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $caption = null;

    // Déclare la date de téléversement.
    #[ORM\Column]
    private \DateTimeImmutable $uploadedAt;

    // Déclare la relation propriétaire vers la galerie.
    #[ORM\ManyToOne(inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'La galerie est obligatoire.')]
    private ?Gallery $gallery = null;

    // Déclare le fichier uploadé non persisté en base.
    #[Assert\File(
        maxSize: '5M',
        maxSizeMessage: 'Le fichier est trop volumineux (max {{ limit }}).',
        mimeTypes: ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
        mimeTypesMessage: 'Format non autorisé. Utilisez JPEG, PNG, WEBP ou GIF.'
    )]
    private ?UploadedFile $imageFile = null;

    // Initialise les valeurs par défaut.
    public function __construct()
    {
        // Initialise la date de téléversement.
        $this->uploadedAt = new \DateTimeImmutable();
    }

    // Retourne l'identifiant.
    public function getId(): ?int
    {
        // Retourne la clé primaire.
        return $this->id;
    }

    // Retourne le chemin de fichier public.
    public function getFilePath(): string
    {
        // Retourne le chemin.
        return $this->filePath;
    }

    // Définit le chemin de fichier public.
    public function setFilePath(string $filePath): self
    {
        // Affecte le chemin nettoyé.
        $this->filePath = trim($filePath);
        // Retourne l'instance courante.
        return $this;
    }

    // Retourne la légende.
    public function getCaption(): ?string
    {
        // Retourne la légende.
        return $this->caption;
    }

    // Définit la légende.
    public function setCaption(?string $caption): self
    {
        // Affecte la légende nettoyée.
        $this->caption = $caption !== null ? trim($caption) : null;
        // Retourne l'instance courante.
        return $this;
    }

    // Retourne la date de téléversement.
    public function getUploadedAt(): \DateTimeImmutable
    {
        // Retourne la date.
        return $this->uploadedAt;
    }

    // Définit explicitement la date de téléversement.
    public function setUploadedAt(\DateTimeImmutable $uploadedAt): self
    {
        // Affecte la date.
        $this->uploadedAt = $uploadedAt;
        // Retourne l'instance courante.
        return $this;
    }

    // Retourne la galerie liée.
    public function getGallery(): ?Gallery
    {
        // Retourne la galerie.
        return $this->gallery;
    }

    // Définit la galerie liée.
    public function setGallery(?Gallery $gallery): self
    {
        // Affecte la galerie.
        $this->gallery = $gallery;
        // Retourne l'instance courante.
        return $this;
    }

    // Retourne le fichier uploadé temporaire.
    public function getImageFile(): ?UploadedFile
    {
        // Retourne le fichier non persisté.
        return $this->imageFile;
    }

    // Définit le fichier uploadé temporaire.
    public function setImageFile(?UploadedFile $imageFile): self
    {
        // Affecte le fichier uploadé.
        $this->imageFile = $imageFile;
        // Retourne l'instance courante.
        return $this;
    }

    // Retourne une représentation texte utile.
    public function __toString(): string
    {
        // Retourne la légende sinon le chemin.
        return $this->caption ?? $this->filePath;
    }
}

