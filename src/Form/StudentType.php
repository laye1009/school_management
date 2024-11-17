<?php

namespace App\Form;

use App\Entity\Classe;
use App\Entity\Student;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class StudentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom'
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom'
            ])
            ->add('email', TextType::class, [
                'label' => 'Email Scolaire'
            ])
            ->add('classe', EntityType::class, [
                'class' => Classe::class,
                'choice_label' => 'libelle',
            ])
            ->add('Ajouter', SubmitType::class, [
                'attr' => ['class' => 'bg-green-700 text-white font-bold py-2 px-4 rounded mt-2 mr-2']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Student::class,
        ]);
    }
}
