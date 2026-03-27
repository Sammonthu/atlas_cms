<?php
// Déclare le namespace des fixtures.
namespace App\DataFixtures;

// Importe les entités métier.
use App\Entity\Article;
use App\Entity\ArticleCategory;
use App\Entity\Comment;
use App\Entity\Gallery;
use App\Entity\Image;
use App\Entity\Page;
use App\Entity\PageCategory;
use App\Entity\Tag;
use App\Entity\User;
// Importe la classe de base FixturesBundle.
use Doctrine\Bundle\FixturesBundle\Fixture;
// Importe le manager d'objets Doctrine.
use Doctrine\Persistence\ObjectManager;
// Importe le hasher de mot de passe.
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
// Importe le slugger métier.
use App\Service\ContentSlugger;

// Déclare les fixtures de démonstration.
class AppFixtures extends Fixture
{
    // Injecte les services nécessaires.
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ContentSlugger $contentSlugger
    ) {
        // Aucun traitement additionnel.
    }

    // Charge les données de test.
    public function load(ObjectManager $manager): void
    {
        // Crée un utilisateur administrateur.
        $admin = (new User())
            ->setName('Admin Atlas')
            ->setEmail('admin@atlas.local')
            ->setRoles(['ROLE_ADMIN', 'ROLE_EDITOR']);
        // Définit le mot de passe hashé de l'admin.
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin123!'));
        // Persiste l'admin.
        $manager->persist($admin);

        // Crée un utilisateur rédacteur.
        $editor = (new User())
            ->setName('Redacteur Atlas')
            ->setEmail('editor@atlas.local')
            ->setRoles(['ROLE_EDITOR']);
        // Définit le mot de passe hashé du rédacteur.
        $editor->setPassword($this->passwordHasher->hashPassword($editor, 'Editor123!'));
        // Persiste le rédacteur.
        $manager->persist($editor);

        // Crée un utilisateur de test standard.
        $user = (new User())
            ->setName('Utilisateur Test')
            ->setEmail('user@atlas.local')
            ->setRoles(['ROLE_USER']);
        // Définit le mot de passe hashé de l'utilisateur.
        $user->setPassword($this->passwordHasher->hashPassword($user, 'User123!'));
        // Persiste l'utilisateur.
        $manager->persist($user);

        // Crée une catégorie de page.
        $pageCategory = (new PageCategory())
            ->setName('Institutionnel');
        // Persiste la catégorie de page.
        $manager->persist($pageCategory);

        // Crée une catégorie d'article.
        $articleCategory = (new ArticleCategory())
            ->setName('Actualités');
        // Persiste la catégorie d'article.
        $manager->persist($articleCategory);

        // Crée une galerie.
        $gallery = (new Gallery())
            ->setName('Campus 2026')
            ->setDescription('Galerie photos des événements du campus.');
        // Persiste la galerie.
        $manager->persist($gallery);

        // Crée une image de galerie.
        $image = (new Image())
            ->setFilePath('/uploads/campus-01.jpg')
            ->setCaption('Entrée principale du campus')
            ->setGallery($gallery);
        // Persiste l'image.
        $manager->persist($image);

        // Crée une page CMS.
        $page = (new Page())
            ->setTitle('À propos')
            ->setContent('<p>Bienvenue sur le site Atlas CMS.</p>')
            ->setSlug($this->contentSlugger->slugify('A propos'))
            ->setMetaDescription('Découvrez la présentation du projet CMS.')
            ->setModerationStatus('approved')
            ->setCategory($pageCategory)
            ->setGallery($gallery);
        // Persiste la page.
        $manager->persist($page);

        // Crée deux tags.
        $tagSymfony = (new Tag())->setName('Symfony');
        $tagCms = (new Tag())->setName('CMS');
        // Persiste les tags.
        $manager->persist($tagSymfony);
        $manager->persist($tagCms);

        // Crée un article de blog.
        $article = (new Article())
            ->setTitle('Lancement du CMS Atlas')
            ->setContent('<p>Le projet scolaire démarre avec Symfony 7 et EasyAdmin.</p>')
            ->setSlug($this->contentSlugger->slugify('Lancement du CMS Atlas'))
            ->setMetaDescription('Premier article du blog Atlas CMS.')
            ->setModerationStatus('approved')
            ->setPublishedAt(new \DateTimeImmutable())
            ->setCategory($articleCategory)
            ->setAuthor($editor)
            ->addTag($tagSymfony)
            ->addTag($tagCms);
        // Persiste l'article.
        $manager->persist($article);

        // Crée un commentaire approuvé.
        $comment = (new Comment())
            ->setContent('Très bon démarrage, bravo à toute l’équipe.')
            ->setStatus('approved')
            ->setArticle($article)
            ->setAuthor($admin);
        // Persiste le commentaire.
        $manager->persist($comment);

        // Exécute l'écriture SQL finale.
        $manager->flush();
    }
}
