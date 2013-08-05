<?php

namespace Topxia\WebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TeacherProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text', array('required' => false));
        $builder->add('about', 'textarea', array('required' => false));
        $builder->add('qq', 'text', array('required' => false));
        $builder->add('weibo', 'text', array('required' => false));
        $builder->add('blog', 'text', array('required' => false));
        $builder->add('site', 'text', array('required' => false));
    }

    public function getName()
    {
        return 'profile';
    }
}