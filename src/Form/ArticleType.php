<?php

namespace App\Form;

use App\Entity\Article;
use App\Entity\Category;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType as TypeTextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Image;

class ArticleType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options): void
  {
    $builder
      ->add('title', TypeTextType::class, [ // le champ title est de type text
        'label' => 'Titre' // contenu du label associé au champ title
      ])
      ->add('content', TextareaType::class, [
        'label' => 'Contenu'
      ])
      ->add('image', FileType::class, [ // le champ image est de type file
        'label' => 'Image (JPG, PNG, WEBP)',
        'mapped' => false, // Ne pas lier le champ à l'entité
        'required' => false, // Le champ n'est pas obligatoire
        'constraints' => [ // Ajout de contraintes sur le champ image
          new Image([
            'maxSize' => '10240k', // Taille max : 10Mo
            'mimeTypes' => [ // Le fichier doit être de type jpg, png, webp
              'image/jpeg',
              'image/png',
              'image/webp',
            ],
            'mimeTypesMessage' => 'Merci de télécharger une image au format JPG, PNG ou WEBP' // Message d'erreur
          ])
        ]
      ])
      ->add('category', EntityType::class, [
        'class' => Category::class, // L'entité à utiliser
        'choice_label' => 'name', // Le champ à afficher dans le formulaire
        'multiple' => true, // Autoriser plusieurs sélections
        'expanded' => true, // Afficher sous forme de cases à cocher (false = liste déroulante)
      ]);
  }

  public function configureOptions(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'data_class' => Article::class,
    ]);
  }
}
