<?php
// Déclare le namespace du formulaire commentaire.
namespace App\Form;

// Importe l'entité Comment.
use App\Entity\Comment;
// Importe le type de formulaire abstrait.
use Symfony\Component\Form\AbstractType;
// Importe le builder de formulaire.
use Symfony\Component\Form\FormBuilderInterface;
// Importe le type textarea.
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
// Importe le resolver d'options.
use Symfony\Component\OptionsResolver\OptionsResolver;

// Déclare le formulaire public de commentaire.
class CommentType extends AbstractType
{
    // Construit les champs du formulaire.
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Ajoute le champ de contenu.
        $builder->add('content', TextareaType::class, [
            // Définit le label utilisateur.
            'label' => 'Votre commentaire',
            // Ajoute des attributs HTML utiles à l'UX.
            'attr' => [
                'rows' => 5,
                'maxlength' => 3000,
                'class' => 'form-control',
                'placeholder' => 'Partagez votre avis de façon constructive...',
            ],
            // Ajoute une aide pédagogique.
            'help' => 'Le commentaire sera visible après validation par la modération.',
        ]);
    }

    // Configure les options globales du formulaire.
    public function configureOptions(OptionsResolver $resolver): void
    {
        // Lie ce formulaire à l'entité Comment.
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}

