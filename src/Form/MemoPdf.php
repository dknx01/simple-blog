<?php
/**
 * simple-blog
 * User: dknx01 <e.witthauer@gmail.com>
 * Date: 13.08.20
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class MemoPdf extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('pdf', FileType::class, [
            'label' => 'Memo (PDF file)',
            // unmapped means that this field is not associated to any entity property
            'mapped' => false,
            'required' => true,

            // unmapped fields can't define their validation using annotations
            // in the associated entity, so you can use the PHP constraint classes
            'constraints' => [
                new File([
                    'maxSize' => '10240k',
//                    'mimeTypes' => [
//                        'application/pdf',
//                        'application/x-pdf',
//                        'text/markdown',
//                    ],
                    'mimeTypesMessage' => 'max. Upload 10MB',
                ])
            ],
        ])
        ->add('type', TextType::class, [
            'label' => 'Pfad des Memos',
            'mapped' => false,
            'required' => true,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MemoPdf::class,
        ]);
    }
}