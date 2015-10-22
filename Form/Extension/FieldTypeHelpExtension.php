<?php

namespace Niva\Beaver\ControlBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class FieldTypeHelpExtension extends AbstractTypeExtension
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildFor(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('help', $options['help'])
            ->setAttribute('suffix', $options['suffix'])
            ->setAttribute('prefix', $options['prefix'])
            ->setAttribute('s3', $options['s3']);
    }

    /**
     * @param FormView      $view
     * @param FormInterface $form
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['help'] = $options['help'];
        $view->vars['suffix'] = $options['suffix'];
        $view->vars['prefix'] = $options['prefix'];
        $view->vars['s3'] = $options['s3'];
    }

    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'help' => null,
            'suffix' => null,
            'prefix' => null,
            's3' => false,
        ));
    }

    /**
     * @return string
     */
    public function getExtendedType()
    {
        return 'form';
    }
}