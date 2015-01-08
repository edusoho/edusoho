<?php
namespace Topxia\MobileBundleV2\Processor\Impl;

use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\CategoryProcessor;

class CategoryProcessorImpl extends BaseProcessor implements CategoryProcessor
{

    public function getTags()
    {
        $tags = $this->getTagService()->findAllTags(0, 100);
        $tags = array_map(function($tag){
            $tag['createdTime'] = Date('c' , $tag['createdTime']);
            return $tag;
        }, $tags);
        return $tags;
    }

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
        $group = $this->controller->getCategoryService()->getGroupByCode('course');
        if (empty($group)) {
            return array();
        } 

        $categories = $this->controller->getCategoryService()->getCategoryTree($group['id']);

        array_unshift($categories, array(
            "id"=>"0",
            "code"=>"root",
            "name"=>"默认分类",
            "icon"=>"",
            "path"=>"0",
            "weight"=>"0",
            "groupId"=>"0",
            "description"=>"默认分类",
            "depth"=>"0",
            ));
        return $categories;
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