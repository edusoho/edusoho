<?php
namespace Topxia\Service\Taxonomy\Impl;

use Topxia\Service\Taxonomy\CategoryService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class CategoryServiceImpl extends BaseService implements CategoryService
{

    public function createCategory(array $category)
    {
        $category = $this->filterCategory($category);
        $this->checkCategory($category);
        $category = $this->getCategoryDao()->addCategory($category);
        return $this->updateCategoryPath($category);
    }

    public function getCategory($id)
    {
        return $this->getCategoryDao()->getCategory($id);
    }

    public function getCategoryByCode($code)
    {
        return $this->getCategoryDao()->findCategoryByCode($code);
    }

    public function findCategoriesByIds(array $ids)
    {
        return ArrayToolkit::index( $this->getCategoryDao()->findCategoriesByIds($ids), 'id');
    }

    public function updateCategory($id, array $fields)
    {
        return $this->getCategoryDao()->updateCategory($id, $fields);
    }

    public function getCategories($groupId)
    {
        $group = $this->getGroup($groupId);
        if (empty($group)) {
            throw $this->createServiceException("分类Group #{$groupId}，不存在");
        }
        return $this->getCategoryDao()->findCategoriesByGroupId($group['id']);
    }

    public function getCategoryTree($groupId)
    {
        $group = $this->getGroup($groupId);
        if (empty($group)) {
            throw $this->createServiceException("分类Group #{$groupId}，不存在");
        }
        $prepare = function($categories) {
            $prepared = array();
            foreach ($categories as $category) {
                if (!isset($prepared[$category['parentId']])) {
                    $prepared[$category['parentId']] = array();
                }
                $prepared[$category['parentId']][] = $category;
            }
            return $prepared;
        };

        $categories = $prepare($this->getCategories($groupId));

        $tree = array();
        $this->makeCategoryTree($tree, $categories, 0);

        return $tree;
    }

    public function findCategoryChildrenIds($id)
    {
        $category = $this->getCategory($id);
        if (empty($category)) {
            return array();
        }
        $tree = $this->getCategoryTree($category['groupId']);

        $childrenIds = array();
        $depth = 0;
        foreach ($tree as $node) {
            if ($node['id'] == $category['id']) {
                $depth = $node['depth'];
                continue;
            }
            if ($depth > 0 && $depth < $node['depth']) {
                $childrenIds[] = $node['id'];
            }

            if ($depth > 0 && $depth >= $node['depth']) {
                break;
            }

        }

        return $childrenIds;
    }

    public function saveCategory($category)
    {
        if (empty($category['id'])) {
            return $this->createCategory($category);
        } else {
            return $this->updateCategory($category['id'], $category);
        }
    }

    public function deleteCategory($id)
    {
        return $this->getCategoryDao()->deleteCategory($id);
    }

    /**
     * group
     */
    public function getGroup($id)
    {   
        return $this->getGroupDao()->getGroup($id);
    }

    public function getGroupByCode($code)
    {
        return $this->getGroupDao()->findGroupByCode($code);
    }

    public function getGroups($start, $limit)
    {
        return $this->getGroupDao()->findGroups($start, $limit);
    }

    /**
    *分类的分组系统初始化时初始化好，此方法仅仅给单元测试使用
    */
    public function addGroup(array $group)
    {
        return $this->getGroupDao()->addGroup($group);
    }

    private function makeCategoryTree(&$tree, &$categories, $parentId)
    {
        static $depth = 0;
        static $leaf = false;
        if (isset($categories[$parentId]) && is_array($categories[$parentId])) {
            foreach ($categories[$parentId] as $category) {
                $depth++;
                $category['depth'] = $depth;
                $tree[] = $category;
                $this->makeCategoryTree($tree, $categories, $category['id']);
                $depth--;
            }
        }
        return $tree;
    }

    private function updateCategoryPath(array $category)
    {
        return $this->updateCategory($category['id'], array('path' => $this->createCategoryPath($category)));
    }

    private function filterCategory(array $category)
    {
        $legalFields = array('name', 'code', 'weight', 'groupId', 'parentId');
        foreach ($category as $key => $value) {
            if (!in_array($key, $legalFields)) {
                unset($category[$key]);
            }
        }
        $category['parentId'] = empty($category['parentId']) ? 0 : $category['parentId'];
        $category['groupId'] = empty($category['groupId']) ? 0 : $category['groupId'];
        return $category;
    }

    private function checkCategory(array $category)
    {
        if ($category['parentId']) {
            $parentCategory = $this->getCategory($category['parentId']);
            if (empty($parentCategory)) {
                throw $this->createServiceException('父分类不存在');
            }
        }
        if (empty($category['name'])) {
            throw $this->createServiceException('分类名称不能为空');
        }
        if ($category['groupId']) {
            $group = $this->getGroup($category['groupId']);
            if (empty($group)) {
                throw $this->createServiceException('分类Group不存在!');
            }
        }
        if ((!empty($category['weight']) && !is_int($category['weight'])) || $category['weight'] > 999999) {
            throw $this->createServiceException('权重不是整数或者大于99999!');
        }
        $getCategoryByCode = $this->getCategoryByCode($category['code']);
        if (!empty($getCategoryByCode)) {
            throw $this->createServiceException('code已被占用');
        }
    }

    private function createCategoryPath(array $category)
    {
        if (empty($category['id'])) throw $this->createServiceException('分类ID为空，不能创建分类PATH');
        $categories = ArrayToolkit::index($this->getCategories($category['groupId']), 'id');
        $pathArray = array($category['id']);
        $parentId = $category['parentId'];
        while ($parentId) {
            if ($categories[$parentId]) {
                $pathArray[] = $parentId;
                $parentId = $categories[$parentId]['parentId'];
            } else {
                break;
            }
        }
        return implode('|', array_reverse($pathArray));
    }

    private function getCategoryDao ()
    {
        return $this->createDao('Taxonomy.CategoryDao');
    }

    private function getGroupDao()
    {
        return $this->createDao('Taxonomy.CategoryGroupDao');
    }

}