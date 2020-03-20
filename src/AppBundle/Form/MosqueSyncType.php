<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MosqueSyncType extends AbstractType
{
    private $languages;

    /**
     * MosqueSyncType constructor.
     *
     * @param $languages
     */
    public function __construct($languages)
    {
        $this->languages = $languages;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $languages = array_map(function ($lang) {
            return Intl::getLanguageBundle()->getLanguageName($lang);
        }, $this->languages);

        $builder
            ->add('id', IntegerType::class, [
                'required' => true,
                'label' => "mosqueScreen.fillId",
                'attr' => [
                    'help' => "mosqueScreen.fillIdHelp",
                    'class' => 'keyboardInput'
                ]
            ])->add('login', EmailType::class, [
                'required' => true,
                'label' => "mosqueScreen.login",
                'attr' => [
                    'help' => "mosqueScreen.loginHelp",
                    'class' => 'keyboardInput'
                ]
            ])->add('password', PasswordType::class, [
                'required' => true,
                'label' => "mosqueScreen.password",
                'attr' => [
                    'help' => "mosqueScreen.passwordHelp",
                    'class' => 'keyboardInput'
                ]
            ])->add('language', ChoiceType::class, [
                'choices' => array_combine($languages, $this->languages),
                'required' => true,
                'label' => "mosqueScreen.Language.label",
                'placeholder' => "mosqueScreen.Language.placeholder",
            ]);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }

}
