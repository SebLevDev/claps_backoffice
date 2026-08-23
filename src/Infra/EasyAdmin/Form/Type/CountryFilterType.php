<?php

namespace Infra\EasyAdmin\Form\Type;

use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\ChoiceFilterType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Intl\Countries;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CountryFilterType extends AbstractType
{
    public function getParent(): string
    {
        return ChoiceFilterType::class;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'value_type_options' => [
                'choices' => array_flip(Countries::getNames()),
            ],
        ]);
    }
}