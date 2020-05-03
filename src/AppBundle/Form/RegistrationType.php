<?php

namespace AppBundle\Form;

use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'tou',
                CheckboxType::class,
                [
                    'required' => true,
                ]
            )
            ->add(
                'recaptcha',
                EWZRecaptchaType::class,
                [
                    'label' => false
                ]
            );

        $builder->add(
            'plainPassword',
            PasswordType::class,
            [
                'translation_domain' => 'FOSUserBundle',
                'label' => 'form.password',
                'attr' => [
                    'autocomplete' => 'new-password',
                    'pattern' => '^(?=.*?[A-Z])(?=.*?[a-z])(?=.*?[0-9])(?=.*?[^\w\s]).{12,}$',
                ],
            ]
        );
    }


    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'label_format' => 'registration.form.%name%.label',
            )
        );
    }

}
