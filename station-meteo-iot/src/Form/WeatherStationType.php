<?php

namespace App\Form;

use App\Entity\WeatherStation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WeatherStationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('macAddress', TextType::class, [
                'label' => 'Adresse MAC',
                'attr' => [
                    'placeholder' => 'XX:XX:XX:XX:XX:XX',
                    'pattern' => '^([0-9A-F]{2}:){5}[0-9A-F]{2}$'
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'Nom de la station',
                'attr' => ['placeholder' => 'Ex: Station Jardin']
            ])
            ->add('location', TextType::class, [
                'label' => 'Emplacement',
                'attr' => ['placeholder' => 'Ex: Jardin arrière'],
                'required' => false
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['rows' => 3]
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Station active',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => WeatherStation::class,
        ]);
    }
}