<?php
// Déclare le namespace de l'entité page.
namespace App\Entity;

// Importe le mapping ORM Doctrine.
use Doctrine\ORM\Mapping as ORM;
// Importe les contraintes de validation Symfony.
use Symfony\Component\Validator\Constraints as Assert;

// Déclare l'entité Page.
#[ORM\Entity]
// Déclare la table SQL.
#[ORM\Table(name: 'page')]
class Page
{
    // Déclare la clé primaire.
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Déclare le titre de la page.
    #[ORM\Column(length: 190)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 190, maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.')]
    private string $title = '';

    // Déclare le contenu de la page.
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le contenu est obligatoire.')]
    private string $content = '';

    // Déclare le slug SEO de la page.
    #[ORM\Column(length: 220, unique: true)]
    #[Assert\NotBlank(message: 'Le slug est obligatoire.')]
    #[Assert\Regex(pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', message: 'Le slug doit contenir uniquement des lettres minuscules, chiffres et tirets.')]
    private string $slug = '';

    // Déclare la méta-description SEO.
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'La méta-description ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $metaDescription = null;

    // Déclare le statut de modération.
    #[ORM\Column(length: 30)]
    #[Assert\Choice(choices: ['pending', 'approved', 'rejected'], message: 'Le statut de modération est invalide.')]
    private string $moderationStatus = 'pending';

    // Déclare la date de création.
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    // Déclare la date de mise à jour.
    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    // Déclare la relation propriétaire vers la catégorie.
    #[ORM\ManyToOne(inversedBy: 'pages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull(message: 'La catégorie de page est obligatoire.')]
    private ?PageCategory $category = null;

    // Déclare la relation propriétaire vers la galerie.
    #[ORM\ManyToOne(inversedBy: 'pages')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Gallery $gallery = null;

    // Initialise les valeurs par défaut.
    public function __construct()
    {
        // Initialise la date de création.
        $this->createdAt = new \DateTimeImmutable();
        // Initialise la date de mise à jour.
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Retourne l'id.
    public function getId(): ?int
    {
        // Retourne la clé primaire.
        return $this->id;
    }

    // Retourne le titre.
    public function getTitle(): string
    {
        // Retourne le titre.
        return $this->title;
    }

    // Définit le titre.
    public function setTitle(string $title): self
    {
        // Affecte le titre nettoyé.
        $this->title = trim($title);
        // Met à jour la date de modification.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne le contenu.
    public function getContent(): string
    {
        // Retourne le contenu.
        return $this->content;
    }

    // Définit le contenu.
    public function setContent(string $content): self
    {
        // Affecte le contenu.
        $this->content = $content;
        // Met à jour la date de modification.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne le slug.
    public function getSlug(): string
    {
        // Retourne le slug.
        return $this->slug;
    }

    // Définit le slug.
    public function setSlug(string $slug): self
    {
        // Affecte le slug nettoyé.
        $this->slug = trim($slug);
        // Met à jour la date de modification.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne la méta-description.
    public function getMetaDescription(): ?string
    {
        // Retourne la méta-description.
        return $this->metaDescription;
    }

    // Définit la méta-description.
    public function setMetaDescription(?string $metaDescription): self
    {
        // Affecte la méta-description.
        $this->metaDescription = $metaDescription !== null ? trim($metaDescription) : null;
        // Met à jour la date de modification.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne le statut de modération.
    public function getModerationStatus(): string
    {
        // Retourne le statut.
        return $this->moderationStatus;
    }

    // Définit le statut de modération.
    public function setModerationStatus(string $moderationStatus): self
    {
        // Affecte le statut.
        $this->moderationStatus = $moderationStatus;
        // Met à jour la date de modification.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne la date de création.
    public function getCreatedAt(): \DateTimeImmutable
    {
        // Retourne la date de création.
        return $this->createdAt;
    }

    // Retourne la date de mise à jour.
    public function getUpdatedAt(): \DateTimeImmutable
    {
        // Retourne la date de mise à jour.
        return $this->updatedAt;
    }

    // Met à jour le timestamp.
    public function touch(): self
    {
        // Affecte la date courante.
        $this->updatedAt = new \DateTimeImmutable();
        // Retourne l'instance.
        return $this;
    }

    // Retourne la catégorie.
    public function getCategory(): ?PageCategory
    {
        // Retourne la catégorie.
        return $this->category;
    }

    // Définit la catégorie.
    public function setCategory(?PageCategory $category): self
    {
        // Affecte la catégorie.
        $this->category = $category;
        // Met à jour la date de modification.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne la galerie.
    public function getGallery(): ?Gallery
    {
        // Retourne la galerie.
        return $this->gallery;
    }

    // Définit la galerie.
    public function setGallery(?Gallery $gallery): self
    {
        // Affecte la galerie.
        $this->gallery = $gallery;
        // Met à jour la date de modification.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne une représentation texte.
    public function __toString(): string
    {
        // Retourne le titre.
        return $this->title;
    }
}

