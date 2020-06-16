<?php

namespace App\Form;

use App\Entity\Person;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $readonly = $options['readonly'];
        $builder
            ->add('NAN', null, [
                'disabled' => $readonly,
                'constraints' => [
                    new \App\Validator\IsValidDNI(),
                    new NotBlank(),
                ],
                'label' => 'person.NAN',
            ])
            ->add('izena', null, [
                'label' => 'person.izena',
                'disabled' => $readonly,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('abizenak', null, [
                'label' => 'person.abizenak',
                'disabled' => $readonly,
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('telefonoa', null, [
                'label' => 'person.telefonoa',
                'disabled' => $readonly,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Person::class,
            'readonly' => false,
        ]);
    }
}
