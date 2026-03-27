<?php
// Déclare le namespace du service d'upload image.
namespace App\Service;

// Importe le type UploadedFile Symfony.
use Symfony\Component\HttpFoundation\File\UploadedFile;
// Importe le composant Filesystem Symfony.
use Symfony\Component\Filesystem\Filesystem;

// Déclare le service dédié à l'upload sécurisé des images.
class ImageUploadService
{
    // Injecte le dossier cible absolu des images galerie.
    public function __construct(
        private readonly string $galleryUploadDirectory,
        private readonly string $projectDir
    )
    {
        // Ne réalise aucun traitement complémentaire au constructeur.
    }

    // Téléverse un fichier image et retourne le chemin public stocké.
    public function uploadGalleryImage(UploadedFile $uploadedFile): string
    {
        // Crée une instance Filesystem.
        $filesystem = new Filesystem();
        // Vérifie l'existence du dossier cible.
        if (!$filesystem->exists($this->galleryUploadDirectory)) {
            // Crée le dossier cible de manière sécurisée.
            $filesystem->mkdir($this->galleryUploadDirectory, 0755);
        }

        // Détermine l'extension réelle proposée par Symfony.
        $safeExtension = $uploadedFile->guessExtension();
        // Définit une extension de secours si indéterminée.
        $extension = $safeExtension !== null ? $safeExtension : 'bin';
        // Génère un nom de fichier aléatoire sécurisé.
        $safeFilename = sprintf('%s.%s', bin2hex(random_bytes(16)), $extension);

        // Déplace le fichier vers le dossier cible.
        $uploadedFile->move($this->galleryUploadDirectory, $safeFilename);

        // Retourne le chemin public relatif stocké en base.
        return '/uploads/galleries/' . $safeFilename;
    }

    // Supprime un ancien fichier image s'il existe.
    public function removeGalleryImage(?string $publicPath): void
    {
        // Vérifie qu'un chemin public est fourni.
        if ($publicPath === null || $publicPath === '') {
            // Quitte sans action si aucun chemin exploitable.
            return;
        }

        // Nettoie le chemin public pour obtenir un chemin relatif.
        $relativePath = ltrim($publicPath, '/');
        // Construit le chemin absolu du fichier cible.
        $absolutePath = $this->projectDir . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . $relativePath;

        // Crée une instance Filesystem.
        $filesystem = new Filesystem();
        // Vérifie que le fichier existe réellement.
        if ($filesystem->exists($absolutePath)) {
            // Supprime le fichier du disque.
            $filesystem->remove($absolutePath);
        }
    }
}
