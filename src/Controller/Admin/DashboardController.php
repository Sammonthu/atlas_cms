<?php
// DÃ©clare le namespace du dashboard admin.
namespace App\Controller\Admin;

// Importe l'entitÃ© Article.
use App\Entity\Article;
// Importe l'entitÃ© ArticleCategory.
use App\Entity\ArticleCategory;
// Importe l'entitÃ© Comment.
use App\Entity\Comment;
// Importe l'entitÃ© Gallery.
use App\Entity\Gallery;
// Importe l'entitÃ© Image.
use App\Entity\Image;
// Importe l'entitÃ© Page.
use App\Entity\Page;
// Importe l'entitÃ© PageCategory.
use App\Entity\PageCategory;
// Importe l'entitÃ© Tag.
use App\Entity\Tag;
// Importe l'entitÃ© User.
use App\Entity\User;
// Importe l'attribut AdminDashboard.
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
// Importe la configuration Action EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
// Importe la configuration Assets EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
// Importe la configuration Dashboard EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
// Importe la configuration MenuItem EasyAdmin.
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
// Importe le contrÃ´leur dashboard abstrait.
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
// Importe le gÃ©nÃ©rateur d'URL admin.
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
// Importe l'EntityManager Doctrine.
use Doctrine\ORM\EntityManagerInterface;
// Importe la rÃ©ponse HTTP.
use Symfony\Component\HttpFoundation\Response;
// Importe l'attribut de sÃ©curitÃ©.
use Symfony\Component\Security\Http\Attribute\IsGranted;

// DÃ©clare le dashboard EasyAdmin avec route dÃ©diÃ©e.
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
// Autorise l'accÃ¨s au dashboard pour Ã©diteur et admin.
#[IsGranted('ROLE_EDITOR')]
class DashboardController extends AbstractDashboardController
{
    // Injecte l'EntityManager et le gÃ©nÃ©rateur d'URL admin.
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {
        // Ne rÃ©alise aucun traitement complÃ©mentaire.
    }

    // Affiche la page dashboard personnalisÃ©e.
    public function index(): Response
    {
        // Calcule les compteurs globaux de contenus.
        $stats = [
            // Compte le nombre d'utilisateurs.
            'users' => $this->entityManager->getRepository(User::class)->count([]),
            // Compte le nombre de pages.
            'pages' => $this->entityManager->getRepository(Page::class)->count([]),
            // Compte le nombre d'articles.
            'articles' => $this->entityManager->getRepository(Article::class)->count([]),
            // Compte le nombre de commentaires.
            'comments' => $this->entityManager->getRepository(Comment::class)->count([]),
            // Compte le nombre de galeries.
            'galleries' => $this->entityManager->getRepository(Gallery::class)->count([]),
            // Compte le nombre d'images.
            'images' => $this->entityManager->getRepository(Image::class)->count([]),
        ];

        // Calcule les Ã©lÃ©ments en attente de modÃ©ration.
        $moderation = [
            // Compte les pages en attente.
            'pendingPages' => $this->entityManager->getRepository(Page::class)->count(['moderationStatus' => 'pending']),
            // Compte les articles en attente.
            'pendingArticles' => $this->entityManager->getRepository(Article::class)->count(['moderationStatus' => 'pending']),
            // Compte les commentaires en attente.
            'pendingComments' => $this->entityManager->getRepository(Comment::class)->count(['status' => 'pending']),
        ];

        // Construit les raccourcis de navigation du dashboard.
        $quickLinks = [
            // GÃ©nÃ¨re l'URL index des pages.
            'pages' => $this->generateCrudUrl(PageCrudController::class),
            // GÃ©nÃ¨re l'URL index des articles.
            'articles' => $this->generateCrudUrl(ArticleCrudController::class),
            // GÃ©nÃ¨re l'URL index des commentaires.
            'comments' => $this->generateCrudUrl(CommentCrudController::class),
            // GÃ©nÃ¨re l'URL index des galeries.
            'galleries' => $this->generateCrudUrl(GalleryCrudController::class),
            // GÃ©nÃ¨re l'URL index des utilisateurs.
            'users' => $this->generateCrudUrl(UserCrudController::class),
        ];

        // Rend le template dashboard personnalisÃ©.
        return $this->render('admin/dashboard.html.twig', [
            // Passe les statistiques globales.
            'stats' => $stats,
            // Passe les donnÃ©es de modÃ©ration.
            'moderation' => $moderation,
            // Passe les URLs rapides.
            'quickLinks' => $quickLinks,
        ]);
    }

    // Configure l'identitÃ© visuelle du dashboard.
    public function configureDashboard(): Dashboard
    {
        // Retourne la configuration gÃ©nÃ©rale.
        return Dashboard::new()
            // DÃ©finit un titre premium lisible.
            ->setTitle('Atlas CMS - Administration')
            // DÃ©finit la taille maximale de contenu.
            ->renderContentMaximized();
    }

    // Configure les assets spÃ©cifiques au back-office.
    public function configureAssets(): Assets
    {
        // Retourne la configuration d'assets.
        return Assets::new()
            // Ajoute la feuille CSS dÃ©diÃ©e Ã  l'admin.
            ->addCssFile('styles/admin.css');
    }

    // Configure les Ã©lÃ©ments du menu latÃ©ral.
    public function configureMenuItems(): iterable
    {
        // Ajoute le lien de retour vers le site public.
        yield MenuItem::linkToRoute('Retour au site', 'fa fa-arrow-left', 'home');
        // Ajoute une section dashboard.
        yield MenuItem::section('Tableau de Bord');
        // Ajoute un lien interne vers l'accueil admin.
        yield MenuItem::linkToDashboard('Vue d\'ensemble', 'fa fa-chart-line');
        // Ajoute une section contenus.
        yield MenuItem::section('Contenus');
        // Ajoute le CRUD pages.
        yield MenuItem::linkTo(PageCrudController::class, 'Pages', 'fa fa-file-lines');
        // Ajoute le CRUD catÃ©gories de page rÃ©servÃ© admin.
        yield MenuItem::linkTo(PageCategoryCrudController::class, 'Categories de Page', 'fa fa-folder-tree')->setPermission('ROLE_ADMIN');
        // Ajoute le CRUD articles.
        yield MenuItem::linkTo(ArticleCrudController::class, 'Articles', 'fa fa-newspaper');
        // Ajoute le CRUD catÃ©gories d'article rÃ©servÃ© admin.
        yield MenuItem::linkTo(ArticleCategoryCrudController::class, 'Categories d\'Article', 'fa fa-layer-group')->setPermission('ROLE_ADMIN');
        // Ajoute le CRUD tags rÃ©servÃ© admin.
        yield MenuItem::linkTo(TagCrudController::class, 'Tags', 'fa fa-tags')->setPermission('ROLE_ADMIN');
        // Ajoute le CRUD commentaires.
        yield MenuItem::linkTo(CommentCrudController::class, 'Commentaires', 'fa fa-comments');
        // Ajoute une section mÃ©dias.
        yield MenuItem::section('Medias');
        // Ajoute le CRUD galeries.
        yield MenuItem::linkTo(GalleryCrudController::class, 'Galeries', 'fa fa-images');
        // Ajoute le CRUD images.
        yield MenuItem::linkTo(ImageCrudController::class, 'Images', 'fa fa-image');
        // Ajoute une section systÃ¨me.
        yield MenuItem::section('Systeme');
        // Ajoute le CRUD utilisateurs rÃ©servÃ© admin.
        yield MenuItem::linkTo(UserCrudController::class, 'Utilisateurs', 'fa fa-users')->setPermission('ROLE_ADMIN');
        // Ajoute l'action de dÃ©connexion.
        yield MenuItem::linkToLogout('Deconnexion', 'fa fa-right-from-bracket');
    }

    // GÃ©nÃ¨re une URL CRUD index pour un contrÃ´leur donnÃ©.
    private function generateCrudUrl(string $crudController): string
    {
        // Clone le gÃ©nÃ©rateur pour Ã©viter les effets de bord d'Ã©tat.
        $generator = clone $this->adminUrlGenerator;
        // Construit et retourne l'URL de la liste CRUD.
        return $generator
            ->setController($crudController)
            ->setAction(Action::INDEX)
            ->generateUrl();
    }
}
