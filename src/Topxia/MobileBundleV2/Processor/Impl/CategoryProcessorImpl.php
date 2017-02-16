<?php
namespace Topxia\MobileBundleV2\Processor\Impl;

use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\CategoryProcessor;
use Topxia\Common\ArrayToolkit;

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
                $categories = array_values($categories);
            }
        }

        return $categories;
    }

    public function getCategorieTree()
    {
        $type = $this->getParam('type', 'course');

        $group = $this->controller->getCategoryService()->getGroupByCode($type);

        if (empty($group)) {
            return array();
        }

        $categories = $this->controller->getCategoryService()->getCategoryStructureTree($group['id']);

        return array(
            "realityDepth" => $this->getRealityDepthByGroupId($group['id']),
            "depth"        => $group["depth"],
            "data"         => $categories
            );
    }

    protected function getRealityDepthByGroupId($groupId)
    {
        $categories = $this->controller->getCategoryService()->getCategoryTree($groupId);

        $depths = ArrayToolkit::column($categories, 'depth');

        return max($depths);
    }

    public function getAllCategories()
    {
        $group = $this->controller->getCategoryService()->getGroupByCode('course');
        if (empty($group)) {
            return array();
        } 

        $categories = $this->controller->getCategoryService()->getCategoryStructureTree($group['id']);

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