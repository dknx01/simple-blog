<?php

namespace App\Form;

use App\Entity\NewDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('Path', TextType::class, [
            'label' => 'Speicherort und Memoname',
        ])
            ->add('Content', TextareaType::class, [
                'label' => 'Inhalt',
                'attr' => [
                    'rows' => 50
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => NewDocument::class,
        ]);
    }
}
