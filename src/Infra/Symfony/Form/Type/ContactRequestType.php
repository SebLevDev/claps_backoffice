<?php

declare(strict_types=1);

namespace Infra\Symfony\Form\Type;

use Domain\Contact\ContactRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, [
                'label' => 'Nom complet',
                'attr' => ['placeholder' => 'Votre nom'],
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-mail',
                'attr' => ['placeholder' => 'votre@email.com'],
            ])
            ->add('subject', ChoiceType::class, [
                'label' => 'Sujet',
                'choices' => [
                    'Renseignements généraux' => 'Renseignements généraux',
                    'Inscription à un cours' => 'Inscription à un cours',
                    'Stage / workshop' => 'Stage / workshop',
                    'Demande de prestation' => 'Demande de prestation',
                ],
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message',
                'attr' => ['placeholder' => 'Votre message...'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactRequest::class,
        ]);
    }
}
