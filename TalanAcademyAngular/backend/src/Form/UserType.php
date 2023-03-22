<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    const LABEL = 'label';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastName', TextType::class, [
                self::LABEL => false,
                'attr' => ['placeholder' => 'Nom'],
            ])
            ->add('firstName', TextType::class, [
                self::LABEL => false,
                'attr' => ['placeholder' => 'Prénom'],
            ])
            ->add('email', EmailType::class, [
                self::LABEL => false,
                'attr' => ['placeholder' => 'Email'],
                'data_class' => null
            ]);
//            ->add('password', RepeatedType::class, [
//                'type' => PasswordType::class,
//                'invalid_message' => 'les mots de passe doivent correspondre',
//                'first_options' => ['attr' => ['placeholder' => 'Mot de passe'], self::LABEL => false],
//                'second_options' => ['attr' => ['placeholder' => 'Répéter mot de passe'], self::LABEL => false]
//            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => false,
        ]);
    }
}

{

}