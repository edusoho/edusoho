<?php

namespace Topxia\WebBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class MessageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('receiver', 'text');
        $builder->add('content', 'textarea');
    }

    public function getName()
    {
        return 'message';
    }
}