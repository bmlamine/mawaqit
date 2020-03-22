<?php

namespace AppBundle\Form;

use AppBundle\Entity\Mosque;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StreamType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add('streamUrl', UrlType::class, [
            'attr' => [
                'placeholder' => 'mosque.form.streamUrl.placeholder',
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'required' => false,
            'label_format' => 'mosque.form.%name%.label',
            'data_class' => Mosque::class,
        ));
    }

    public function getBlockPrefix()
    {
        return '';
    }

}
