<?php

namespace App\Form;

use App\Entity\Student;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class StudentType extends AbstractType
{
    const LABEL = 'label';
    const CONSTRAINTS = 'constraints';

      public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('lastName', TextType::class, [
                self::LABEL => 'Nom ',
                self::CONSTRAINTS => [
                    new Length([
                        'min' => '3',
                        'minMessage' => 'Le nom doit comporter au moins {{ limit }} caractères.',
                        'max' => '20',
                        'maxMessage' => 'Le nom ne peut pas contenir plus de {{ limit }} caractères.'
                    ]),
                    new NotBlank([
                        'message' => 'Vous devez entrer un Nom'
                    ])
                ],
                'required' => true
            ])
            ->add('firstName', TextType::class, [
                self::LABEL => 'Prénom ',
                self::CONSTRAINTS => [
                    new Length([
                        'min' => '3',
                        'minMessage' => 'Le prénom doit comporter au moins {{ limit }} caractères.',
                        'max' => '20',
                        'maxMessage' => 'Le prénom ne peut pas contenir plus de {{ limit }} caractères.'
                    ]),
                    new NotBlank([
                        'message' => 'Vous devez entrer un Prénom'
                    ])
                ],
                'required' => true
            ])
            ->add('tel', TelType::class, [
                self::LABEL => 'Téléphone ',
                self::CONSTRAINTS => [
                    new Regex([
                        'pattern' => "/^[0-9]{8}$/",
                        'message' => 'Vous devez entrer un numéro de téléphone composé de 8 chiffres.'
                    ])
                ],
                'required' => true
            ])

            ->add('linkedin', UrlType::class, [
                self::LABEL => 'linkedin ',
                self::CONSTRAINTS => [
                    new Regex([
                        'pattern' => "/^http(s)?:\/\/([\w]+\.)?linkedin\.com\/in\/[A-z0-9_-]+\/?/",
                        'message' => 'Vous devez entrer un lien linkedin valide.'
                    ])
                ],
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Student::class,
            'csrf_protection' => false,
        ]);
    }
}
