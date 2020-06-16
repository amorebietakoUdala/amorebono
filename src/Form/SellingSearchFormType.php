<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SellingSearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('NAN', null, [
                'label' => 'person.NAN',
            ])
            ->add('fromDate', null, [
                'label' => 'selling.fromDate',
            ])
            ->add('toDate', null, [
                'label' => 'selling.toDate',
            ])
            ->add('bonus', \Symfony\Bridge\Doctrine\Form\Type\EntityType::class, [
                'class' => \App\Entity\Bonus::class,
                'placeholder' => 'selling.selectBonus',
                'label' => 'selling.bonusType',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
