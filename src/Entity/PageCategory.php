<?php
// Déclare le namespace de l'entité catégorie de page.
namespace App\Entity;

// Importe les classes de collection Doctrine.
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
// Importe le mapping ORM Doctrine.
use Doctrine\ORM\Mapping as ORM;
// Importe les contraintes de validation Symfony.
use Symfony\Component\Validator\Constraints as Assert;

// Déclare l'entité PageCategory.
#[ORM\Entity]
// Déclare la table SQL.
#[ORM\Table(name: 'page_category')]
class PageCategory
{
    // Déclare la clé primaire.
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Déclare le nom de catégorie.
    #[ORM\Column(length: 150, unique: true)]
    #[Assert\NotBlank(message: 'Le nom de catégorie est obligatoire.')]
    #[Assert\Length(max: 150, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    private string $name = '';

    // Déclare la relation inverse vers les pages.
    #[ORM\OneToMany(mappedBy: 'category', targetEntity: Page::class)]
    private Collection $pages;

    // Initialise les collections.
    public function __construct()
    {
        // Initialise la collection des pages.
        $this->pages = new ArrayCollection();
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
        // Retourne l'instance.
        return $this;
    }

    // Retourne la collection des pages.
    public function getPages(): Collection
    {
        // Retourne la collection.
        return $this->pages;
    }

    // Retourne une représentation texte.
    public function __toString(): string
    {
        // Retourne le nom de catégorie.
        return $this->name;
    }
}

