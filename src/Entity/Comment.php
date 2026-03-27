<?php
// Déclare le namespace de l'entité commentaire.
namespace App\Entity;

// Importe le mapping ORM Doctrine.
use Doctrine\ORM\Mapping as ORM;
// Importe les contraintes de validation Symfony.
use Symfony\Component\Validator\Constraints as Assert;

// Déclare l'entité Comment.
#[ORM\Entity]
// Déclare la table SQL des commentaires.
#[ORM\Table(name: 'cms_comment')]
class Comment
{
    // Déclare la constante de statut en attente.
    public const STATUS_PENDING = 'pending';
    // Déclare la constante de statut approuvé.
    public const STATUS_APPROVED = 'approved';
    // Déclare la constante de statut refusé.
    public const STATUS_REJECTED = 'rejected';

    // Déclare la clé primaire.
    #[ORM\Id]
    // Active l'auto-incrément.
    #[ORM\GeneratedValue]
    // Déclare la colonne id.
    #[ORM\Column]
    private ?int $id = null;

    // Déclare le contenu du commentaire.
    #[ORM\Column(type: 'text')]
    #[Assert\NotBlank(message: 'Le contenu du commentaire est obligatoire.')]
    #[Assert\Length(min: 3, minMessage: 'Le commentaire doit contenir au moins {{ limit }} caractères.')]
    #[Assert\Length(max: 3000, maxMessage: 'Le commentaire ne peut pas dépasser {{ limit }} caractères.')]
    private string $content = '';

    // Déclare la date de création.
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    // Déclare le statut de modération.
    #[ORM\Column(length: 30)]
    #[Assert\Choice(
        choices: [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED],
        message: 'Le statut est invalide.'
    )]
    private string $status = self::STATUS_PENDING;

    // Déclare la relation propriétaire vers l'article.
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'L’article est obligatoire.')]
    private ?Article $article = null;

    // Déclare la relation propriétaire vers l'auteur.
    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Assert\NotNull(message: 'L’auteur est obligatoire.')]
    private ?User $author = null;

    // Initialise les valeurs par défaut.
    public function __construct()
    {
        // Initialise la date de création.
        $this->createdAt = new \DateTimeImmutable();
        // Initialise le statut en attente.
        $this->status = self::STATUS_PENDING;
    }

    // Retourne l'identifiant.
    public function getId(): ?int
    {
        // Retourne la clé primaire.
        return $this->id;
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
        // Affecte le contenu nettoyé.
        $this->content = trim($content);
        // Retourne l'instance.
        return $this;
    }

    // Retourne la date de création.
    public function getCreatedAt(): \DateTimeImmutable
    {
        // Retourne la date.
        return $this->createdAt;
    }

    // Retourne le statut de modération.
    public function getStatus(): string
    {
        // Retourne le statut.
        return $this->status;
    }

    // Définit le statut de modération.
    public function setStatus(string $status): self
    {
        // Affecte le statut.
        $this->status = $status;
        // Retourne l'instance.
        return $this;
    }

    // Vérifie si le commentaire est approuvé.
    public function isApproved(): bool
    {
        // Retourne vrai si statut approuvé.
        return $this->status === self::STATUS_APPROVED;
    }

    // Vérifie si le commentaire est en attente.
    public function isPending(): bool
    {
        // Retourne vrai si statut en attente.
        return $this->status === self::STATUS_PENDING;
    }

    // Retourne l'article lié.
    public function getArticle(): ?Article
    {
        // Retourne l'article.
        return $this->article;
    }

    // Définit l'article lié.
    public function setArticle(?Article $article): self
    {
        // Affecte l'article.
        $this->article = $article;
        // Retourne l'instance.
        return $this;
    }

    // Retourne l'auteur lié.
    public function getAuthor(): ?User
    {
        // Retourne l'auteur.
        return $this->author;
    }

    // Définit l'auteur lié.
    public function setAuthor(?User $author): self
    {
        // Affecte l'auteur.
        $this->author = $author;
        // Retourne l'instance.
        return $this;
    }

    // Retourne une représentation texte utile en admin.
    public function __toString(): string
    {
        // Retourne un extrait du commentaire.
        return mb_substr($this->content, 0, 80);
    }
}

