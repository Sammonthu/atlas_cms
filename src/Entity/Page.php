<?php
// Declare le namespace de l'entite page.
namespace App\Entity;

// Importe les classes de collection Doctrine.
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
// Importe le mapping ORM Doctrine.
use Doctrine\ORM\Mapping as ORM;
// Importe les contraintes de validation Symfony.
use Symfony\Component\Validator\Constraints as Assert;

// Declare l'entite Page.
#[ORM\Entity]
// Declare la table SQL.
#[ORM\Table(name: 'page')]
class Page
{
    // Declare la cle primaire.
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Declare le titre de la page.
    #[ORM\Column(length: 190)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 190, maxMessage: 'Le titre ne peut pas depasser {{ limit }} caracteres.')]
    private string $title = '';

    // Declare le contenu de la page.
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le contenu est obligatoire.')]
    private string $content = '';

    // Declare le slug SEO de la page.
    #[ORM\Column(length: 220, unique: true)]
    #[Assert\NotBlank(message: 'Le slug est obligatoire.')]
    #[Assert\Regex(pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', message: 'Le slug doit contenir uniquement des lettres minuscules, chiffres et tirets.')]
    private string $slug = '';

    // Declare la meta-description SEO.
    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(max: 255, maxMessage: 'La meta-description ne peut pas depasser {{ limit }} caracteres.')]
    private ?string $metaDescription = null;

    // Declare le statut de moderation.
    #[ORM\Column(length: 30)]
    #[Assert\Choice(choices: ['pending', 'approved', 'rejected'], message: 'Le statut de moderation est invalide.')]
    private string $moderationStatus = 'pending';

    // Declare la date de creation.
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    // Declare la date de mise a jour.
    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    // Declare la relation proprietaire vers la categorie.
    #[ORM\ManyToOne(inversedBy: 'pages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull(message: 'La categorie de page est obligatoire.')]
    private ?PageCategory $category = null;

    // Declare la relation proprietaire vers la galerie.
    #[ORM\ManyToOne(inversedBy: 'pages')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Gallery $gallery = null;

    // Declare la relation vers la page parente pour l'arborescence.
    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?self $parent = null;

    // Declare la relation inverse vers les pages enfants.
    #[ORM\OneToMany(mappedBy: 'parent', targetEntity: self::class)]
    #[ORM\OrderBy(['title' => 'ASC'])]
    private Collection $children;

    // Initialise les valeurs par defaut.
    public function __construct()
    {
        // Initialise la collection des pages enfants.
        $this->children = new ArrayCollection();
        // Initialise la date de creation.
        $this->createdAt = new \DateTimeImmutable();
        // Initialise la date de mise a jour.
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Retourne l'id.
    public function getId(): ?int
    {
        // Retourne la cle primaire.
        return $this->id;
    }

    // Retourne le titre.
    public function getTitle(): string
    {
        // Retourne le titre.
        return $this->title;
    }

    // Definit le titre.
    public function setTitle(string $title): self
    {
        // Affecte le titre nettoye.
        $this->title = trim($title);
        // Met a jour la date de modification.
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

    // Definit le contenu.
    public function setContent(string $content): self
    {
        // Affecte le contenu.
        $this->content = $content;
        // Met a jour la date de modification.
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

    // Definit le slug.
    public function setSlug(string $slug): self
    {
        // Affecte le slug nettoye.
        $this->slug = trim($slug);
        // Met a jour la date de modification.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne la meta-description.
    public function getMetaDescription(): ?string
    {
        // Retourne la meta-description.
        return $this->metaDescription;
    }

    // Definit la meta-description.
    public function setMetaDescription(?string $metaDescription): self
    {
        // Affecte la meta-description.
        $this->metaDescription = $metaDescription !== null ? trim($metaDescription) : null;
        // Met a jour la date de modification.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne le statut de moderation.
    public function getModerationStatus(): string
    {
        // Retourne le statut.
        return $this->moderationStatus;
    }

    // Definit le statut de moderation.
    public function setModerationStatus(string $moderationStatus): self
    {
        // Affecte le statut.
        $this->moderationStatus = $moderationStatus;
        // Met a jour la date de modification.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne la date de creation.
    public function getCreatedAt(): \DateTimeImmutable
    {
        // Retourne la date de creation.
        return $this->createdAt;
    }

    // Retourne la date de mise a jour.
    public function getUpdatedAt(): \DateTimeImmutable
    {
        // Retourne la date de mise a jour.
        return $this->updatedAt;
    }

    // Met a jour le timestamp.
    public function touch(): self
    {
        // Affecte la date courante.
        $this->updatedAt = new \DateTimeImmutable();
        // Retourne l'instance.
        return $this;
    }

    // Retourne la categorie.
    public function getCategory(): ?PageCategory
    {
        // Retourne la categorie.
        return $this->category;
    }

    // Definit la categorie.
    public function setCategory(?PageCategory $category): self
    {
        // Affecte la categorie.
        $this->category = $category;
        // Met a jour la date de modification.
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

    // Definit la galerie.
    public function setGallery(?Gallery $gallery): self
    {
        // Affecte la galerie.
        $this->gallery = $gallery;
        // Met a jour la date de modification.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne la page parente.
    public function getParent(): ?self
    {
        // Retourne la page parente.
        return $this->parent;
    }

    // Definit la page parente.
    public function setParent(?self $parent): self
    {
        // Evite de definir la page elle-meme comme parent.
        $this->parent = $parent === $this ? null : $parent;
        // Met a jour la date de modification.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne les pages enfants.
    public function getChildren(): Collection
    {
        // Retourne la collection des pages enfants.
        return $this->children;
    }

    // Ajoute une page enfant.
    public function addChild(self $child): self
    {
        // Verifie si l'enfant est deja present.
        if (!$this->children->contains($child)) {
            // Ajoute l'enfant a la collection.
            $this->children->add($child);
            // Associe le parent sur l'enfant.
            $child->setParent($this);
        }

        // Retourne l'instance.
        return $this;
    }

    // Supprime une page enfant.
    public function removeChild(self $child): self
    {
        // Retire l'enfant de la collection.
        if ($this->children->removeElement($child) && $child->getParent() === $this) {
            // Retire la reference parent sur l'enfant.
            $child->setParent(null);
        }

        // Retourne l'instance.
        return $this;
    }

    // Retourne une representation texte.
    public function __toString(): string
    {
        // Retourne le titre.
        return $this->title;
    }
}