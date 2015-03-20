<?php
namespace Topxia\WebBundle\Form\Common;


use Topxia\WebBundle\Form\DataTransformer\TagsToIdsTransformer;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TagsType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new TagsToIdsTransformer());
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'tags';
    }
}