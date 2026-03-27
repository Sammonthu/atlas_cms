<?php
// Déclare le namespace des commandes Symfony.
namespace App\Command;

// Importe l'entité utilisateur.
use App\Entity\User;
// Importe l'EntityManager Doctrine.
use Doctrine\ORM\EntityManagerInterface;
// Importe la commande abstraite Symfony.
use Symfony\Component\Console\Command\Command;
// Importe l'attribut AsCommand.
use Symfony\Component\Console\Attribute\AsCommand;
// Importe les helpers d'entrée.
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
// Importe les helpers de sortie.
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
// Importe le hasher de mot de passe.
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Déclare la commande de création d'admin.
#[AsCommand(name: 'app:create-admin', description: 'Crée un utilisateur administrateur Atlas CMS.')]
class CreateAdminCommand extends Command
{
    // Injecte les dépendances nécessaires.
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher
    ) {
        // Appelle le constructeur parent.
        parent::__construct();
    }

    // Configure les arguments CLI.
    protected function configure(): void
    {
        // Ajoute l'argument email.
        $this->addArgument('email', InputArgument::REQUIRED, 'Email de connexion');
        // Ajoute l'argument mot de passe.
        $this->addArgument('password', InputArgument::REQUIRED, 'Mot de passe en clair');
        // Ajoute l'argument nom affiché.
        $this->addArgument('name', InputArgument::OPTIONAL, 'Nom complet', 'Admin Atlas');
    }

    // Exécute la commande.
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Instancie l'outil d'affichage console.
        $io = new SymfonyStyle($input, $output);

        // Récupère l'email fourni.
        $email = (string) $input->getArgument('email');
        // Récupère le mot de passe fourni.
        $plainPassword = (string) $input->getArgument('password');
        // Récupère le nom fourni.
        $name = (string) $input->getArgument('name');

        // Vérifie qu'aucun utilisateur n'existe avec cet email.
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => mb_strtolower($email)]);
        if ($existingUser instanceof User) {
            // Affiche une erreur explicite.
            $io->error('Un utilisateur existe déjà avec cet email.');
            // Retourne un code d'échec.
            return Command::FAILURE;
        }

        // Crée un nouvel utilisateur.
        $user = new User();
        // Affecte l'email.
        $user->setEmail($email);
        // Affecte le nom affiché.
        $user->setName($name);
        // Affecte les rôles administrateur.
        $user->setRoles(['ROLE_ADMIN', 'ROLE_EDITOR']);
        // Hash le mot de passe en clair.
        $user->setPassword($this->passwordHasher->hashPassword($user, $plainPassword));

        // Persiste l'utilisateur.
        $this->entityManager->persist($user);
        // Exécute le flush SQL.
        $this->entityManager->flush();

        // Affiche un message de succès.
        $io->success(sprintf('Administrateur créé: %s', $user->getEmail()));
        // Retourne un code de succès.
        return Command::SUCCESS;
    }
}
