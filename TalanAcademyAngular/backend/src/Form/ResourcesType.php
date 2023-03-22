<?php

namespace App\Form;

use App\Entity\Resources;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class ResourcesType extends AbstractType
{
    const LABEL = 'label';

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('ref', HiddenType::class)
            ->add('title', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis.'
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Le titre doit comporter au moins 3 caractères',
                        'max'=> 50,
                        'maxMessage' => 'Le titre doit comporter au maximum 50 caractères',
                    ])

                ]
            ])
            ->add('url', TextType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Ce champ est requis.'
                    ])
                ]
            ])
            ->add('comment', TextareaType::class, [
                'required'=>false,
                self::LABEL => 'Remarques'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Resources::class,
            'csrf_protection' => false,
        ]);
    }
}

{

}