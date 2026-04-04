<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => ['placeholder' => 'Titre de l\'événement', 'class' => 'admin-input'],
                'constraints' => [
                    new NotBlank(message: 'Le titre est obligatoire.'),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['placeholder' => 'Description de l\'événement...', 'class' => 'admin-input', 'rows' => 4],
            ])
            ->add('date', DateTimeType::class, [
                'label' => 'Date et Heure',
                'widget' => 'single_text',
                'attr' => ['class' => 'admin-input'],
                'constraints' => [
                    new NotBlank(message: 'La date est obligatoire.'),
                ],
            ])
            ->add('location', TextType::class, [
                'label' => 'Lieu',
                'attr' => ['placeholder' => 'Lieu de l\'événement', 'class' => 'admin-input'],
                'constraints' => [
                    new NotBlank(message: 'Le lieu est obligatoire.'),
                ],
            ])
            ->add('seats', IntegerType::class, [
                'label' => 'Nombre de places',
                'required' => false,
                'attr' => ['placeholder' => 'Laisser vide pour illimité', 'class' => 'admin-input', 'min' => 0],
                'constraints' => [
                    new GreaterThanOrEqual(value: 0, message: 'Le nombre de places doit être positif.'),
                ],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image de l\'événement',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'admin-input admin-file-input', 'accept' => 'image/*'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez sélectionner une image valide (JPG, PNG, GIF, WEBP).',
                    ]),
                ],
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix (TND)',
                'required' => false,
                'currency' => 'TND',
                'attr' => ['placeholder' => '0.00', 'class' => 'admin-input'],
                'constraints' => [
                    new GreaterThanOrEqual(value: 0, message: 'Le prix doit être positif.'),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}
