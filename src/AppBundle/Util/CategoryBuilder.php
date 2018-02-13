<?php

namespace AppBundle\Util;

use Topxia\Service\Common\ServiceKernel;

class CategoryBuilder
{
    public function buildChoices($groupCode, $indent = '　')
    {
        $group = $this->getCategoryService()->getGroupByCode($groupCode);
        if (empty($group)) {
            return array();
        }

        $choices = array();
        $categories = $this->getCategoryService()->getCategoryTree($group['id']);

        foreach ($categories as $category) {
            $choices[$category['id']] = str_repeat(is_null($indent) ? '' : $indent, ($category['depth'] - 1)).$category['name'];
        }

        return $choices;
    }

    private function getCategoryService()
    {
        return self::getServiceKernel()->getBiz()->service('Taxonomy:CategoryService');
    }

    protected static function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
