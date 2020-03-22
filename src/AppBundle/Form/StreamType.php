<?php

namespace AppBundle\Form;

use AppBundle\Entity\Mosque;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Url;

class StreamType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('streamUrl', UrlType::class, [
                'required' => false,
                'constraints' => new Url(),
                'attr' => [
                    'placeholder' => 'mosque.form.streamUrl.placeholder',
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'save',
                'attr' => [
                    'class' => 'btn btn-primary',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'label_format' => 'mosque.form.%name%.label',
            'data_class' => Mosque::class,
        ));
    }

    public function getBlockPrefix()
    {
        return '';
    }

}
