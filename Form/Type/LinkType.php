<?php

namespace Tec\Ayt\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tec\Ayt\CoreBundle\Entity\Link;

class LinkType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Tec\Ayt\CoreBundle\Entity\Link',
            'mode' => '',
        ));
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('url', 'url')
            ->add('description', 'text')
            ->add('save', 'submit');
    }

    public function getName()
    {
        return 'link';
    }
}