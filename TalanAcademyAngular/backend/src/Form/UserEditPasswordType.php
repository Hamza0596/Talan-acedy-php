<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Regex;


class UserEditPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('password', PasswordType::Class, [
                'constraints' => [
                    new Regex([
                        'pattern' => '/\S*(?=\S{8,})(?=\S*[a-z A-Z])(?=\S*[\d])\S*$/',
                        'message' => 'mot de passe doit contenir au moins un chiffre et une lettre et au minimum 8 caractères'
                    ])
                ]
            ]);


    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'allow_extra_fields'=>true
        ]);
    }

}
