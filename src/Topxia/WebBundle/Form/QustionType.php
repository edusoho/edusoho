<?php
namespace Topxia\WebBundle\Form;

use Topxia\WebBundle\Form\Util\TaxonomyTermChoices;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\AbstractType;

class QustionType extends AbstractType
{

    public function buildForm (FormBuilderInterface $builder, array $options)
    {
        $builder->add('title', 'text');
        $builder->add('content', 'textarea');
        $builder->add('castweibo','checkbox');
    }

    public function getName ()
    {
        return 'qustion';
    }

}