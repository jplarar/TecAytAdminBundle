<?php

namespace Tec\Ayt\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tec\Ayt\CoreBundle\Entity\Event;

class EventType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Tec\Ayt\CoreBundle\Entity\Event',
            'mode' => '',
        ));
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder
            ->add('name', 'text')
            ->add('date', 'text')
            ->add('description', 'text')
            ->add('file', 'file', array(
                'required' => false
            ))
            ->add('save', 'submit');
    }

    public function getName()
    {
        return 'event';
    }
}