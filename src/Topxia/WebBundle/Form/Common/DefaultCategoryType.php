<?php
namespace Topxia\WebBundle\Form\Common;

class DefaultCategoryType extends AbstractCategoryType
{
	protected $group = 'default';

    public function getName()
    {
        return 'default_category';
    }

}