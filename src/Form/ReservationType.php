<?php

namespace App\Form;

use App\Entity\Reservation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;

class ReservationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom Complet',
                'attr' => ['placeholder' => 'Entrez votre nom complet...', 'class' => 'form-input'],
                'constraints' => [
                    new NotBlank(message: 'Le nom est obligatoire.'),
                    new Length(min: 2, minMessage: 'Le nom doit comporter au moins {{ limit }} caractères.')
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse Email',
                'attr' => ['placeholder' => 'exemple@gmail.com', 'class' => 'form-input'],
                'constraints' => [
                    new NotBlank(message: 'L\'adresse email est obligatoire.'),
                    new Email(message: 'Veuillez entrer une adresse email valide.')
                ]
            ])
            ->add('phone', TelType::class, [
                'label' => 'Numéro de Téléphone',
                'attr' => ['placeholder' => 'Ex: +216 ** *** ***', 'class' => 'form-input'],
                'constraints' => [
                    new NotBlank(message: 'Le numéro de téléphone est obligatoire.')
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reservation::class,
        ]);
    }
}
