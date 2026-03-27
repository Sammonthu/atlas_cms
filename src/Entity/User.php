<?php
// Déclare le namespace de l'entité utilisateur.
namespace App\Entity;

// Importe la collection Doctrine.
use Doctrine\Common\Collections\ArrayCollection;
// Importe l'interface de collection Doctrine.
use Doctrine\Common\Collections\Collection;
// Importe les attributs ORM Doctrine.
use Doctrine\ORM\Mapping as ORM;
// Importe les contraintes de validation Symfony.
use Symfony\Component\Validator\Constraints as Assert;
// Importe la contrainte d'unicité Doctrine/Symfony.
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
// Importe l'interface utilisateur Symfony.
use Symfony\Component\Security\Core\User\UserInterface;
// Importe l'interface mot de passe Symfony.
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

// Déclare l'entité User.
#[ORM\Entity]
// Déclare le nom de table SQL.
#[ORM\Table(name: 'cms_user')]
// Valide l'unicité de l'email au niveau application.
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // Déclare la clé primaire.
    #[ORM\Id]
    // Active l'auto-incrément.
    #[ORM\GeneratedValue]
    // Déclare la colonne SQL.
    #[ORM\Column]
    private ?int $id = null;

    // Déclare le nom complet de l'utilisateur.
    #[ORM\Column(length: 160)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    #[Assert\Length(max: 160, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    private string $name = '';

    // Déclare l'email de connexion.
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'L’email est obligatoire.')]
    #[Assert\Email(message: 'Le format de l’email est invalide.')]
    private string $email = '';

    // Déclare le mot de passe hashé.
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le mot de passe est obligatoire.')]
    #[Assert\Length(min: 8, minMessage: 'Le mot de passe doit contenir au moins {{ limit }} caractères.')]
    private string $password = '';

    // Déclare les rôles de sécurité.
    #[ORM\Column(type: 'json')]
    private array $roles = [];

    // Déclare la date de création.
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    // Déclare la date de mise à jour.
    #[ORM\Column]
    private \DateTimeImmutable $updatedAt;

    // Déclare la relation inverse vers les articles.
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Article::class)]
    private Collection $articles;

    // Déclare la relation inverse vers les commentaires.
    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Comment::class)]
    private Collection $comments;

    // Initialise les valeurs par défaut.
    public function __construct()
    {
        // Initialise la collection d'articles.
        $this->articles = new ArrayCollection();
        // Initialise la collection de commentaires.
        $this->comments = new ArrayCollection();
        // Initialise la date de création.
        $this->createdAt = new \DateTimeImmutable();
        // Initialise la date de mise à jour.
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Retourne l'identifiant.
    public function getId(): ?int
    {
        // Retourne la clé primaire.
        return $this->id;
    }

    // Retourne le nom.
    public function getName(): string
    {
        // Retourne le nom utilisateur.
        return $this->name;
    }

    // Définit le nom.
    public function setName(string $name): self
    {
        // Affecte le nom nettoyé.
        $this->name = trim($name);
        // Met à jour le timestamp.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne l'email.
    public function getEmail(): string
    {
        // Retourne l'email.
        return $this->email;
    }

    // Définit l'email.
    public function setEmail(string $email): self
    {
        // Affecte l'email normalisé.
        $this->email = mb_strtolower(trim($email));
        // Met à jour le timestamp.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne l'identifiant de sécurité.
    public function getUserIdentifier(): string
    {
        // Utilise l'email comme identifiant.
        return $this->email;
    }

    // Retourne le mot de passe hashé.
    public function getPassword(): string
    {
        // Retourne le hash.
        return $this->password;
    }

    // Définit le mot de passe hashé.
    public function setPassword(string $password): self
    {
        // Affecte le hash.
        $this->password = $password;
        // Met à jour le timestamp.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Retourne les rôles.
    public function getRoles(): array
    {
        // Copie les rôles existants.
        $roles = $this->roles;
        // Force le rôle utilisateur de base.
        $roles[] = 'ROLE_USER';
        // Retourne les rôles uniques.
        return array_values(array_unique($roles));
    }

    // Définit les rôles.
    public function setRoles(array $roles): self
    {
        // Affecte les rôles.
        $this->roles = $roles;
        // Met à jour le timestamp.
        $this->touch();
        // Retourne l'instance.
        return $this;
    }

    // Nettoie les secrets temporaires.
    public function eraseCredentials(): void
    {
        // Aucun secret temporaire stocké.
    }

    // Retourne la date de création.
    public function getCreatedAt(): \DateTimeImmutable
    {
        // Retourne la date.
        return $this->createdAt;
    }

    // Retourne la date de mise à jour.
    public function getUpdatedAt(): \DateTimeImmutable
    {
        // Retourne la date.
        return $this->updatedAt;
    }

    // Met à jour la date de modification.
    public function touch(): self
    {
        // Affecte la date courante.
        $this->updatedAt = new \DateTimeImmutable();
        // Retourne l'instance.
        return $this;
    }

    // Retourne les articles associés.
    public function getArticles(): Collection
    {
        // Retourne la collection d'articles.
        return $this->articles;
    }

    // Retourne les commentaires associés.
    public function getComments(): Collection
    {
        // Retourne la collection de commentaires.
        return $this->comments;
    }

    // Retourne une représentation texte utile en admin.
    public function __toString(): string
    {
        // Retourne le nom et l'email.
        return sprintf('%s <%s>', $this->name, $this->email);
    }
}
