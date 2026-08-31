<?php

declare(strict_types=1);

namespace Infra\Symfony\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('currentPassword', PasswordType::class, [
                'constraints' => [
                    new UserPassword(),
                ],
                'label' => 'user.change_password.form.label.current_password',
                'attr' => [
                    'autocomplete' => 'off',
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'constraints' => [
                    new NotBlank(),
                    new Length(min: 6, max: 64),
                ],
                'first_options' => [
                    'label' => 'user.change_password.form.label.new_password.first',
                ],
                'second_options' => [
                    'label' => 'user.change_password.form.label.new_password.confirm',
                ],
            ])
        ;
    }
}
