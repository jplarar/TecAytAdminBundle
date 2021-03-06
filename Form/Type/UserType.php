<?php

namespace Tec\Ayt\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

use Tec\Ayt\CoreBundle\Entity\User;

class UserType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Tec\Ayt\CoreBundle\Entity\User',
            'mode' => '',
            'password' => false
        ));
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['password']) {
            $builder
                ->add('password', 'repeated', array(
                    'first_name' => 'password',
                    'second_name' => 'confirm',
                    'type' => 'password',
                    'mapped' => false
                ))
                ->add('submit', 'submit');
        } else {
            $builder
                ->add('username', 'text')
                ->add('fullName', 'text', array(
                    'required' => false
                ));
            if($options['mode'] == 'new') {
                $builder
                    ->add('password', 'repeated', array(
                        'first_name' => 'password',
                        'second_name' => 'confirm',
                        'type' => 'password'
                    ));
            }
            $builder
                ->add('role', 'choice', array(
                    'choices' => User::getAvailableRoles()
                ))
                ->add('email', 'email')
                ->add('birthDate', 'text')
                ->add('gender', 'choice', array(
                    'choices'   => array('1' => 'Male',
                        '0' => 'Female')
                ))
                ->add('state', 'text', array(
                    'attr' => array(
                        'placeholder' => 'Nuevo León',
                    ),
                ))
                ->add('city', 'text', array(
                    'attr' => array(
                        'placeholder' => 'Monterrey',
                    ),
                ))
                ->add('work', 'text', array(
                    'label' => 'Professional Experience'
                ))
                ->add('education', 'choice', array(
                    'choices'   => User::getAvailableEducations()
                ))
                ->add('isActive', 'choice', array(
                    'choices'   => array('1' => 'Active',
                        '0' => 'Inactive')
                ))
                ->add('submit', 'submit');
        }
    }

    public function getName()
    {
        return 'user';
    }
}