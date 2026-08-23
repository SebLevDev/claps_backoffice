<?php

declare(strict_types=1);

namespace Infra\Symfony\Form\Type;

use Domain\Contact\RegistrationRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Prénom',
                'attr' => ['placeholder' => 'Prénom'],
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Nom',
                'attr' => ['placeholder' => 'Nom de famille'],
            ])
            ->add('birthDate', DateType::class, [
                'label' => 'Date de naissance',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('section', ChoiceType::class, [
                'label' => 'Section souhaitée',
                'required' => false,
                'placeholder' => false,
                'choices' => [
                    'Petits (4-7 ans)' => 'Petits (4-7 ans)',
                    'Juniors (7-12 ans)' => 'Juniors (7-12 ans)',
                    'Jeunes (12-20 ans)' => 'Jeunes (12-20 ans)',
                    'Adultes' => 'Adultes',
                    'Adultes perfectionnement' => 'Adultes perfectionnement',
                    'Danses israéliennes' => 'Danses israéliennes',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'attr' => ['placeholder' => 'email@exemple.com'],
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['placeholder' => '+32 ...'],
            ])
            ->add('additionalInfo', TextareaType::class, [
                'label' => 'Informations complémentaires',
                'required' => false,
                'attr' => ['placeholder' => 'Allergies, besoins particuliers, questions...'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RegistrationRequest::class,
        ]);
    }
}
