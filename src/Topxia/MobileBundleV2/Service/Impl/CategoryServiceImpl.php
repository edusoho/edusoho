<?php
namespace Topxia\MobileBundleV2\Service\Impl;

use Topxia\MobileBundleV2\Service\BaseService;
use Topxia\MobileBundleV2\Service\CategoryService;

class CategoryServiceImpl extends BaseService implements CategoryService
{
    public function getCategorys()
    {
        $category = $this->getParam("category");
        if (empty($category)) {
            return $this->controller->getCategoryService()->findAllCategories();
        }

        $group= $this->controller->getCategoryService()->getCategoryByCode($category);

        if (empty($group)) {
            $categories = array();
        } else {
            if ($this->controller->getCategoryService()->getGroupByCode($group['code'])){
                $categories = $this->controller->getCategoryService()->findCategories($group['id']);
            } else {
                $categories = array();
            }
        }

        return $categories;
    }
}