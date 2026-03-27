<?php
// Déclare le namespace de l'entité galerie.
namespace App\Entity;

// Importe les classes de collection Doctrine.
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
// Importe le mapping ORM Doctrine.
use Doctrine\ORM\Mapping as ORM;
// Importe les contraintes de validation Symfony.
use Symfony\Component\Validator\Constraints as Assert;

// Déclare l'entité Gallery.
#[ORM\Entity]
// Déclare la table SQL.
#[ORM\Table(name: 'gallery')]
class Gallery
{
    // Déclare la clé primaire.
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Déclare le nom de la galerie.
    #[ORM\Column(length: 190)]
    #[Assert\NotBlank(message: 'Le nom de la galerie est obligatoire.')]
    #[Assert\Length(max: 190, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    private string $name = '';

    // Déclare la description de la galerie.
    #[ORM\Column(type: 'text', nullable: true)]
    #[Assert\Length(max: 5000, maxMessage: 'La description est trop longue.')]
    private ?string $description = null;

    // Déclare la date de création.
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    // Déclare la date de mise à jour.
    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    // Déclare la relation inverse vers les pages.
    #[ORM\OneToMany(mappedBy: 'gallery', targetEntity: Page::class)]
    private Collection $pages;

    // Déclare la relation inverse vers les images.
    #[ORM\OneToMany(mappedBy: 'gallery', targetEntity: Image::class)]
    private Collection $images;

    // Initialise les valeurs par défaut.
    public function __construct()
    {
        // Initialise la collection des pages.
        $this->pages = new ArrayCollection();
        // Initialise la collection des images.
        $this->images = new ArrayCollection();
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

    // Retourne le nom.
    public function getName(): string
    {
        // Retourne le nom.
        return $this->name;
    }

    // Définit le nom.
    public function setName(string $name): self
    {
        // Affecte le nom nettoyé.
        $this->name = trim($name);
        // Met à jour la date de modification.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne la description.
    public function getDescription(): ?string
    {
        // Retourne la description.
        return $this->description;
    }

    // Définit la description.
    public function setDescription(?string $description): self
    {
        // Affecte la description.
        $this->description = $description !== null ? trim($description) : null;
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

    // Retourne les pages liées.
    public function getPages(): Collection
    {
        // Retourne la collection.
        return $this->pages;
    }

    // Retourne les images liées.
    public function getImages(): Collection
    {
        // Retourne la collection.
        return $this->images;
    }

    // Retourne une représentation texte.
    public function __toString(): string
    {
        // Retourne le nom.
        return $this->name;
    }
}

