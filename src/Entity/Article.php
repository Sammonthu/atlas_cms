<?php
// Déclare le namespace de l'entité article.
namespace App\Entity;

// Importe les classes de collection Doctrine.
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
// Importe le mapping ORM Doctrine.
use Doctrine\ORM\Mapping as ORM;
// Importe les contraintes de validation Symfony.
use Symfony\Component\Validator\Constraints as Assert;

// Déclare l'entité Article.
#[ORM\Entity]
// Déclare la table SQL.
#[ORM\Table(name: 'article')]
class Article
{
    // Déclare la clé primaire.
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Déclare le titre de l'article.
    #[ORM\Column(length: 190)]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 190, maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.')]
    private string $title = '';

    // Déclare le contenu de l'article.
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le contenu est obligatoire.')]
    private string $content = '';

    // Déclare la date de publication.
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $publishedAt = null;

    // Déclare le slug SEO de l'article.
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
    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull(message: 'La catégorie d’article est obligatoire.')]
    private ?ArticleCategory $category = null;

    // Déclare la relation propriétaire vers l'auteur.
    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    #[Assert\NotNull(message: 'L’auteur est obligatoire.')]
    private ?User $author = null;

    // Déclare la relation propriétaire vers les tags.
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'articles')]
    #[ORM\JoinTable(name: 'article_tag')]
    private Collection $tags;

    // Déclare la relation inverse vers les commentaires.
    #[ORM\OneToMany(mappedBy: 'article', targetEntity: Comment::class)]
    private Collection $comments;

    // Initialise les valeurs par défaut.
    public function __construct()
    {
        // Initialise la collection de tags.
        $this->tags = new ArrayCollection();
        // Initialise la collection de commentaires.
        $this->comments = new ArrayCollection();
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

    // Retourne la date de publication.
    public function getPublishedAt(): ?\DateTimeImmutable
    {
        // Retourne la date.
        return $this->publishedAt;
    }

    // Définit la date de publication.
    public function setPublishedAt(?\DateTimeImmutable $publishedAt): self
    {
        // Affecte la date.
        $this->publishedAt = $publishedAt;
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
    public function getCategory(): ?ArticleCategory
    {
        // Retourne la catégorie.
        return $this->category;
    }

    // Définit la catégorie.
    public function setCategory(?ArticleCategory $category): self
    {
        // Affecte la catégorie.
        $this->category = $category;
        // Met à jour la date de modification.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne l'auteur.
    public function getAuthor(): ?User
    {
        // Retourne l'auteur.
        return $this->author;
    }

    // Définit l'auteur.
    public function setAuthor(?User $author): self
    {
        // Affecte l'auteur.
        $this->author = $author;
        // Met à jour la date de modification.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne les tags.
    public function getTags(): Collection
    {
        // Retourne la collection.
        return $this->tags;
    }

    // Ajoute un tag.
    public function addTag(Tag $tag): self
    {
        // Vérifie si le tag est déjà présent.
        if (!$this->tags->contains($tag)) {
            // Ajoute le tag.
            $this->tags->add($tag);
        }
        // Retourne l'instance.
        return $this;
    }

    // Supprime un tag.
    public function removeTag(Tag $tag): self
    {
        // Supprime le tag.
        $this->tags->removeElement($tag);
        // Retourne l'instance.
        return $this;
    }

    // Retourne les commentaires.
    public function getComments(): Collection
    {
        // Retourne la collection.
        return $this->comments;
    }

    // Retourne une représentation texte.
    public function __toString(): string
    {
        // Retourne le titre.
        return $this->title;
    }
}

