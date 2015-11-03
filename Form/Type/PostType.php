<?php

namespace Tec\Ayt\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tec\Ayt\CoreBundle\Entity\Post;

class PostType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Tec\Ayt\CoreBundle\Entity\Post',
            'mode' => '',
        ));
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('title', 'text')
            ->add('content', 'textarea')
            ->add('author', 'text')
            ->add('file', 'file', array(
                'required' => false
            ))
            ->add('save', 'submit');
    }

    public function getName()
    {
        return 'post';
    }
}