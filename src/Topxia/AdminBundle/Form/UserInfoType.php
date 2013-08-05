<?php

namespace Topxia\AdminBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('roles', 'user_role', array('multiple' => true, 'expanded' =>  true));
    }

    public function getName()
    {
        return 'userinfo';
    }
}