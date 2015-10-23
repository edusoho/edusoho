<?php

namespace Topxia\WebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('objectType', 'hidden');
        $builder->add('objectId', 'hidden');
        $builder->add('content', 'textarea');
    }

    public function getName()
    {
        return 'comment';
    }
}