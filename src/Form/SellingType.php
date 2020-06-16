<?php

namespace App\Form;

use App\Entity\Selling;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\NotBlank;

class SellingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $readonly = $options['readonly'];
        $builder
            ->add('quantity', null, [
                'data' => 1,
                'label' => 'selling.quantity',
                'attr' => ['min' => 1],
                'disabled' => $readonly,
            ])
            ->add('totalPrice', null, [
                'disabled' => true,
                'label' => 'selling.totalPrice',
            ])
            ->add('sellingDate', null, [
                'disabled' => $readonly,
            ])
            ->add('person', PersonType::class, [
                'disabled' => $readonly,
            ])
            ->add('bonus', EntityType::class, [
                'class' => \App\Entity\Bonus::class,
                'placeholder' => 'selling.selectBonus',
                'label' => 'selling.bonus',
                'constraints' => [
                    new NotBlank(),
                ],
                'disabled' => $readonly,
            ])
            ->add('serialNumber', null, [
                'disabled' => $readonly,
                'label' => 'selling.serialNumber',
                'constraints' => [
                    new NotBlank(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Selling::class,
            'readonly' => false,
        ]);
    }
}
