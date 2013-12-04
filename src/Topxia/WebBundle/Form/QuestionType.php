<?php

namespace Topxia\WebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CourseType extends AbstractType
{

    public function buildForm (FormBuilderInterface $builder, array $options)
    {
        
    }

    public function getName ()
    {
        return 'course';
    }

}