<?php

namespace App\Form;

use App\Entity\Memo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Title', TextType::class, [
                'label' => 'Titel'
            ])
            ->add('Type', ChoiceType::class, [
                'label' => 'Typ',
                'choices'  => [
                    'LV' => 'LV',
                    'OV TK' => 'OV TK'
                ]
            ])
            ->add('Date', DateType::class, [
                'label' => 'Datum',
                'widget' => 'single_text',
            ])
            ->add('Location', TextType::class, [
                'label' => 'Ort'
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
            'data_class' => Memo::class,
        ]);
    }
}
