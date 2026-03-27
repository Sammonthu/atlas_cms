<?php
// Déclare le namespace du formulaire galerie.
namespace App\Form;

// Importe l'entité Gallery.
use App\Entity\Gallery;
// Importe le type de formulaire abstrait.
use Symfony\Component\Form\AbstractType;
// Importe le builder de formulaire.
use Symfony\Component\Form\FormBuilderInterface;
// Importe le type textarea.
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
// Importe le type text.
use Symfony\Component\Form\Extension\Core\Type\TextType;
// Importe le resolver d'options.
use Symfony\Component\OptionsResolver\OptionsResolver;

// Déclare le formulaire de gestion des galeries.
class GalleryType extends AbstractType
{
    // Construit les champs du formulaire.
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Ajoute le champ nom.
        $builder->add('name', TextType::class, [
            // Définit le label utilisateur.
            'label' => 'Nom de la galerie',
            // Ajoute un placeholder utile.
            'attr' => ['placeholder' => 'Exemple : Voyage scolaire 2026'],
        ]);

        // Ajoute le champ description.
        $builder->add('description', TextareaType::class, [
            // Définit le label utilisateur.
            'label' => 'Description',
            // Rend le champ facultatif.
            'required' => false,
            // Ajoute des attributs UX.
            'attr' => [
                'rows' => 4,
                'placeholder' => 'Décrivez brièvement la galerie...',
            ],
        ]);
    }

    // Configure les options du formulaire.
    public function configureOptions(OptionsResolver $resolver): void
    {
        // Lie le formulaire à l'entité Gallery.
        $resolver->setDefaults([
            'data_class' => Gallery::class,
        ]);
    }
}

