<?php

namespace Topxia\WebBundle\Form;

use Topxia\WebBundle\Form\Util\TaxonomyTermChoices;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class CourseType extends AbstractType
{

    public function buildForm (FormBuilderInterface $builder, array $options)
    {
        $builder->add('type', 'choice', array(
            'choices' => array('online' => '在线课程', 'offline' => '线下课程'),
            'expanded' => true
        ));
        $builder->add('title', 'text');
        $builder->add('tags', 'tags');
        $builder->add('categoryId', 'hidden');
        $builder->add('price', 'number', array(
            'precision' => 2,
        ));
        $builder->add('startTime', 'datetime', array(
            'input' => 'timestamp',
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd HH:mm'
        ));
        $builder->add('endTime', 'datetime', array(
            'input'  => 'timestamp',
            'widget' => 'single_text',
            'format' => 'yyyy-MM-dd HH:mm'
        ));
        $builder->add('locationId', 'hidden');
        $builder->add('address', 'text');
    }

    public function getName ()
    {
        return 'course';
    }

}