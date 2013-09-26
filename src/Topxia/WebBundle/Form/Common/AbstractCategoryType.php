<?php
namespace Topxia\WebBundle\Form\Common;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Topxia\WebBundle\Util\CategoryBuilder;

abstract class AbstractCategoryType extends AbstractType
{
    protected $group;

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $builder = new CategoryBuilder();
        $resolver->setDefaults(array(
            'choices' => $builder->buildChoices($this->group),
        ));
    }

    public function getParent()
    {
        return 'choice';
    }

    public function getName()
    {
        return 'category';
    }
}