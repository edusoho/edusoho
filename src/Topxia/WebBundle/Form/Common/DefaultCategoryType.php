<?php
namespace Topxia\WebBundle\Form\Common;

class DefaultCategoryType extends AbstractCategoryType
{
	protected $group = 'course';

    public function getName()
    {
        return 'default_category';
    }

}