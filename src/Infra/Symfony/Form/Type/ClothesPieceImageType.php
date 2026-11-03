<?php

declare(strict_types=1);

namespace Infra\Symfony\Form\Type;

use Infra\Symfony\Persistance\Doctrine\Entity\ClothesPieceImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Vich\UploaderBundle\Form\Type\VichImageType;

/** Une photo d'une pièce de costume dans la collection "Photos" de l'admin. */
class ClothesPieceImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', VichImageType::class, [
                'label' => 'Remplacer la photo',
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                // getPath() gère aussi les images reprises de WordPress (chemin complet en base)
                'image_uri' => static fn (ClothesPieceImage $image) => $image->getPath(),
            ])
            ->add('position', IntegerType::class, [
                'label' => 'Ordre',
                'help' => 'La photo avec le plus petit ordre est la photo principale.',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ClothesPieceImage::class,
        ]);
    }
}
