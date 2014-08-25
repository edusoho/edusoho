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


    public function getAllCategories()
    {
        $categories = $this->controller->getCategoryService()->findAllCategories();
        $newCategories = $this->sortCategories($categories);

        return $newCategories;
    }

    private function sortCategories($categories)
    {
        $newCategories = array();
        $categorieIds = array();
        foreach ($categories as $categorie) {
            $parentId = $categorie["parentId"];
            if (!array_key_exists($parentId, $categorieIds)) {
                $newCategories[] = $this->getCategory($parentId);
                $categorieIds[$parentId] = null;
            }
            $newCategories[] = $categorie;
        }
        return $newCategories;
    }

    private function getCategory($id)
    {
        if (0 == $id) {
            return array(
                "id"=>"0",
                "code"=>"group",
                "name"=>"分组",
                "icon"=>"",
                "path"=>"",
                "weight"=>"0",
                "groupId"=>"0",
                "parentId"=>"0",
                "description"=>null,
                );
        }

        $categorie = $this->controller->getCategoryService()->getCategory($id);
        $categorie["code"] = "group";
        return $categorie;
    }

}