<?php
// Declare le namespace du controleur blog public.
namespace App\Controller;

// Importe l'entite Article.
use App\Entity\Article;
// Importe l'entite Comment.
use App\Entity\Comment;
// Importe l'entite User.
use App\Entity\User;
// Importe le formulaire de commentaire.
use App\Form\CommentType;
// Importe l'EntityManager Doctrine.
use Doctrine\ORM\EntityManagerInterface;
// Importe le logger PSR.
use Psr\Log\LoggerInterface;
// Importe le controleur abstrait Symfony.
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
// Importe l'attribut MapEntity Doctrine bridge.
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
// Importe l'attribut Autowire pour cibler un canal de log.
use Symfony\Component\DependencyInjection\Attribute\Autowire;
// Importe la requete HTTP Symfony.
use Symfony\Component\HttpFoundation\Request;
// Importe la reponse HTTP Symfony.
use Symfony\Component\HttpFoundation\Response;
// Importe l'attribut de route Symfony.
use Symfony\Component\Routing\Attribute\Route;
// Importe l'attribut de securite Symfony.
use Symfony\Component\Security\Http\Attribute\IsGranted;

// Declare le controleur front du blog.
class BlogController extends AbstractController
{
    // Injecte le logger front dedie.
    public function __construct(
        #[Autowire(service: 'monolog.logger.cms_front')]
        private readonly LoggerInterface $frontLogger
    ) {
        // Ne realise aucun traitement additionnel.
    }

    // Declare la route de liste des articles.
    #[Route('/blog', name: 'front_blog_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Recupere les articles valides tries par date.
        $articles = $entityManager->getRepository(Article::class)->findBy(
            ['moderationStatus' => 'approved'],
            ['publishedAt' => 'DESC', 'createdAt' => 'DESC']
        );

        // Construit la reponse Twig de la liste blog.
        $response = $this->render('front/blog/index.html.twig', [
            // Passe les articles a Twig.
            'articles' => $articles,
        ]);

        // Active un cache navigateur prive de courte duree.
        $response->setPrivate();
        // Definit la duree de cache navigateur en secondes.
        $response->setMaxAge(180);

        // Retourne la reponse finale.
        return $response;
    }

    // Declare la route de detail d'article.
    #[Route('/blog/{slug}', name: 'front_blog_show', methods: ['GET'])]
    public function show(
        #[MapEntity(mapping: ['slug' => 'slug'])] Article $article,
        EntityManagerInterface $entityManager
    ): Response {
        // Verifie que l'article est valide.
        if ($article->getModerationStatus() !== 'approved') {
            // Retourne une 404 pour contenu non valide.
            throw $this->createNotFoundException('Article indisponible.');
        }

        // Recupere uniquement les commentaires valides.
        $approvedComments = $entityManager->getRepository(Comment::class)->findBy(
            ['article' => $article, 'status' => Comment::STATUS_APPROVED],
            ['createdAt' => 'DESC']
        );

        // Construit la reponse Twig du detail article.
        $response = $this->render('front/blog/show.html.twig', [
            // Passe l'article au template.
            'article' => $article,
            // Passe la liste des commentaires valides.
            'approvedComments' => $approvedComments,
            // Passe le formulaire de commentaire.
            'commentForm' => $this->createForm(CommentType::class)->createView(),
        ]);

        // Active un cache navigateur prive de courte duree.
        $response->setPrivate();
        // Definit la duree de cache navigateur en secondes.
        $response->setMaxAge(120);

        // Retourne la reponse finale.
        return $response;
    }

    // Declare la route de soumission de commentaire.
    #[Route('/blog/{slug}/comment', name: 'front_blog_comment_create', methods: ['POST'])]
    // Reserve l'action aux utilisateurs connectes.
    #[IsGranted('ROLE_USER')]
    public function postComment(
        #[MapEntity(mapping: ['slug' => 'slug'])] Article $article,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Verifie que l'article est valide.
        if ($article->getModerationStatus() !== 'approved') {
            // Retourne une 404 pour contenu non valide.
            throw $this->createNotFoundException('Article indisponible.');
        }

        // Cree une instance de commentaire.
        $comment = new Comment();
        // Cree le formulaire lie au commentaire.
        $form = $this->createForm(CommentType::class, $comment);
        // Hydrate le formulaire depuis la requete HTTP.
        $form->handleRequest($request);

        // Verifie la soumission valide du formulaire.
        if ($form->isSubmitted() && $form->isValid()) {
            // Associe l'article au commentaire.
            $comment->setArticle($article);
            // Recupere l'utilisateur connecte.
            $user = $this->getUser();
            // Verifie le type utilisateur.
            if ($user instanceof User) {
                // Associe l'auteur au commentaire.
                $comment->setAuthor($user);
            }
            // Place le commentaire en attente de validation.
            $comment->setStatus(Comment::STATUS_PENDING);
            // Persiste le commentaire.
            $entityManager->persist($comment);
            // Execute la transaction SQL.
            $entityManager->flush();

            // Ecrit un log d'audit sur le canal front.
            $this->frontLogger->info('Nouveau commentaire en attente de moderation.', [
                // Logge l'identifiant de l'article concerne.
                'articleId' => $article->getId(),
                // Logge l'identifiant de l'auteur connecte.
                'authorId' => $comment->getAuthor()?->getId(),
            ]);

            // Affiche un message flash de succes.
            $this->addFlash('success', 'Commentaire envoye. Il sera publie apres validation.');
        } else {
            // Affiche un message flash d'erreur en cas de formulaire invalide.
            $this->addFlash('error', 'Impossible d\'enregistrer votre commentaire. Verifiez les champs.');
        }

        // Redirige vers le detail de l'article.
        return $this->redirectToRoute('front_blog_show', ['slug' => $article->getSlug()]);
    }
}
