<?php

namespace Topxia\WebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('truename', 'text', array('required' => false));
        $builder->add('gender', 'gender', array('expanded' => true, 'required' => true));
        $builder->add('birthday', 'birthday', array(
            'input' => 'string',
            'required' => false,
            'empty_value' => array('year' => '年' , 'month' => '月' , 'day' => '日') , 
            'years' => range(date('Y'), date('Y') - 100),
        ));
        $builder->add('city', 'text', array('required' => false));
        $builder->add('mobile', 'text', array('required' => false));
        $builder->add('qq', 'text', array('required' => false));
        // $builder->add('signature', 'textarea', array('required' => false));
        $builder->add('company', 'text', array('required' => false));
        $builder->add('job', 'text', array('required' => false));
        $builder->add('title', 'text', array('required' => false));
        $builder->add('about', 'textarea', array('required' => false));
    }

    public function getName()
    {
        return 'profile';
    }
}