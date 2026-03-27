<?php
// Déclare le namespace du formulaire image.
namespace App\Form;

// Importe l'entité Gallery.
use App\Entity\Gallery;
// Importe l'entité Image.
use App\Entity\Image;
// Importe le type abstrait de formulaire.
use Symfony\Component\Form\AbstractType;
// Importe le builder de formulaire.
use Symfony\Component\Form\FormBuilderInterface;
// Importe le type de sélection d'entité.
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
// Importe le type de fichier.
use Symfony\Component\Form\Extension\Core\Type\FileType;
// Importe le type text.
use Symfony\Component\Form\Extension\Core\Type\TextType;
// Importe le resolver d'options.
use Symfony\Component\OptionsResolver\OptionsResolver;

// Déclare le formulaire de gestion des images.
class ImageType extends AbstractType
{
    // Construit les champs du formulaire.
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Ajoute le champ fichier image.
        $builder->add('imageFile', FileType::class, [
            // Définit le label utilisateur.
            'label' => 'Fichier image',
            // Rend le champ obligatoire selon contexte.
            'required' => $options['require_file'],
            // Ajoute des attributs HTML d'assistance.
            'attr' => [
                'accept' => 'image/jpeg,image/png,image/webp,image/gif',
            ],
        ]);

        // Ajoute le champ légende.
        $builder->add('caption', TextType::class, [
            // Définit le label utilisateur.
            'label' => 'Légende',
            // Rend la légende facultative.
            'required' => false,
            // Ajoute un placeholder utile.
            'attr' => ['placeholder' => 'Exemple : Vue d’ensemble du site'],
        ]);

        // Ajoute la sélection de galerie.
        $builder->add('gallery', EntityType::class, [
            // Définit le label utilisateur.
            'label' => 'Galerie',
            // Cible l'entité Gallery.
            'class' => Gallery::class,
            // Définit le champ affiché.
            'choice_label' => 'name',
        ]);
    }

    // Configure les options du formulaire.
    public function configureOptions(OptionsResolver $resolver): void
    {
        // Définit les options par défaut.
        $resolver->setDefaults([
            // Lie le formulaire à l'entité Image.
            'data_class' => Image::class,
            // Rend le fichier obligatoire par défaut.
            'require_file' => true,
        ]);

        // Déclare le type attendu pour l'option custom.
        $resolver->setAllowedTypes('require_file', 'bool');
    }
}

