<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class BuyBonoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $restantesTipo1 = $options['restantes_tipo1'];
        $restantesTipo2 = $options['restantes_tipo2'];
            $builder
                ->add('cantidad_bonos1', $restantesTipo1 > 0 ? IntegerType::class : HiddenType::class, [
                    'label' => 'Cantidad',
                    'attr' => [
                        'min' => 0,
                        'max' => $restantesTipo1,
                    ],
                    'constraints' => [
                        new Assert\LessThanOrEqual($restantesTipo1,null),
                    ],
                    'empty_data' => 0,
                    'data' => 0,
                ])
                ->add('cantidad_bonos2', $restantesTipo2 > 0 ? IntegerType::class : HiddenType::class, [
                    'label' => 'Cantidad',
                    'attr' => [
                        'min' => 0,
                        'max' => $restantesTipo2,
                    ],
                    'constraints' => [
                        new Assert\LessThanOrEqual($restantesTipo2,null),
                    ],
                    'empty_data' => 0,
                    'data' => 0,
                ])
                ->add('email', EmailType::class, [
                    'label' => 'label.email',
                    'required' => false,
                    'constraints' => [
                        new Assert\Email(),
                    ]
                ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'restantes_tipo1' => 10,
            'restantes_tipo2' => 9,
        ]);
    }
}
