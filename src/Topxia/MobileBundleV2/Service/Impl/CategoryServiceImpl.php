<?php
namespace Topxia\MobileBundleV2\Service\Impl;

use Topxia\MobileBundleV2\Service\BaseService;
use Topxia\MobileBundleV2\Service\CategoryService;

class CategoryServiceImpl extends BaseService implements CategoryService
{
    public function getCategories()
    {
        $category = $this->getParam("category");
        
        if (empty($category)) {
            $categories = $this->controller->getCategoryService()->findGroupRootCategories("course");
        } else {
            $group= $this->controller->getCategoryService()->getCategoryByCode($category);
            if (empty($group)) {
                $categories = array();
            } else {
                $ids = $this->controller->getCategoryService()->findCategoryChildrenIds($group['id']);
                $categories = $this->controller->getCategoryService()->findCategoriesByIds($ids);
            }
        }

        return $categories;
    }
}