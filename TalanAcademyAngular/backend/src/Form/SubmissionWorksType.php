<?php

namespace App\Form;

use App\Entity\SubmissionWorks;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class SubmissionWorksType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('repoLink', TextType::class,
                ['label' => 'Lien repo git',
                    'constraints' =>[
                        new NotBlank([
                            'message' => 'Ce champ est requis.'
                        ])
                        ]
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SubmissionWorks::class,
            'csrf_protection' =>false
        ]);
    }
}
