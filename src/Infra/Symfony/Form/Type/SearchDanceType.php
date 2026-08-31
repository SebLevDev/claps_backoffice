<?php

declare(strict_types=1);

namespace Infra\Symfony\Form\Type;

use Infra\Symfony\Persistance\Doctrine\Entity\Dance;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchDanceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $countries = $options['countries'];
        $countriesChoice = [];
        foreach ($countries as $countryIso) {
            if ($countryIso) {
                $countryLabel = Countries::getName($countryIso);
                $countriesChoice[$countryLabel] = $countryIso;
            }
        }
        ksort($countriesChoice);

        $builder
            ->setMethod('GET')
            ->add('country', ChoiceType::class, [
                'choices' => $countriesChoice,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('hasWorkshopVideo', CheckboxType::class, [
                'required' => false,
                'label' => 'Avec vidéo de stage',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Dance::class,
            'countries' => [],
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        // Formulaire sans préfixe : champs soumis en GET à plat (?country=BE&hasWorkshopVideo=1)
        // pour matcher les clés lues par DanceRepository::filterAllQueryBuilder() (SqlParameterBag).
        return '';
    }
}
