<?php
namespace Topxia\Service\Article\Impl;

use Topxia\Service\Article\CategoryService;
use Topxia\Service\Common\BaseService;
use Topxia\Common\ArrayToolkit;

class CategoryServiceImpl extends BaseService implements CategoryService
{
    public function getCategory($id)
    {
        if (empty($id)) {
            return null;
        }
        return $this->getCategoryDao()->getCategory($id);
    }

    public function getCategoryByCode($code)
    {
        return $this->getCategoryDao()->findCategoryByCode($code);
    }

    public function getCategoryTree()
    {
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

        $categories = $prepare($this->findAllCategories());

        $tree = array();
        $this->makeCategoryTree($tree, $categories, 0);

        return $tree;
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

    public function findCategoryChildrenIds($id)
    {
        $category = $this->getCategory($id);
        if (empty($category)) {
            return array();
        }
        $tree = $this->getCategoryTree();

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

    public function findCategoriesByIds(array $ids)
    {
        return ArrayToolkit::index( $this->getCategoryDao()->findCategoriesByIds($ids), 'id');
    }

    public function findAllCategories()
    {
        return $this->getCategoryDao()->findAllCategories();
    }

    public function isCategoryCodeAvaliable($code, $exclude = null)
    {
        if (empty($code)) {
            return false;
        }

        if ($code == $exclude) {
            return true;
        }

        $category = $this->getCategoryDao()->findCategoryByCode($code);

        return $category ? false : true;
    }

    public function createCategory(array $category)
    {
        $category = ArrayToolkit::parts($category, array('name', 'code', 'weight'
            ,'parentId', 'publishArticle' ,'pagesize','seoTitle','seoKeyword'
            ,'seoDesc','published','type','templateName','urlNameRule','comment'));

        if (!ArrayToolkit::requireds($category, array('name', 'code', 'weight', 'parentId'))) {
            throw $this->createServiceException("缺少必要参数，，添加栏目失败");
        }

        $this->filterCategoryFields($category);

        $category = $this->getCategoryDao()->addCategory($category);

        $this->getLogService()->info('category', 'create', "添加栏目 {$category['name']}(#{$category['id']})", $category);

        return $category;
    }

    public function updateCategory($id, array $fields)
    {
        $category = $this->getCategory($id);
        if (empty($category)) {
            throw $this->createNoteFoundException("栏目(#{$id})不存在，更新栏目失败！");
        }

        $fields = ArrayToolkit::parts($fields, array('name', 'code', 'weight', 'parentId', 'publishArticle' ,'pagesize','seoTitle','seoKeyword'
            ,'seoDesc','published','type','templateName','urlNameRule','comment'));
        if (empty($fields)) {
            throw $this->createServiceException('参数不正确，更新栏目失败！');
        }

        $this->filterCategoryFields($fields, $category);

        $this->getLogService()->info('category', 'update', "编辑栏目 {$fields['name']}(#{$id})", $fields);

        return $this->getCategoryDao()->updateCategory($id, $fields);
    }

    public function deleteCategory($id)
    {
        $category = $this->getCategory($id);
        if (empty($category)) {
            throw $this->createNotFoundException();
        }

        $ids = $this->findCategoryChildrenIds($id);
        $ids[] = $id;
        foreach ($ids as $id) {
            $this->getCategoryDao()->deleteCategory($id);
        }

        $this->getLogService()->info('category', 'delete', "删除栏目{$category['name']}(#{$id})");
    }

    private function filterCategoryFields(&$category, $releatedCategory = null)
    {
        foreach (array_keys($category) as $key) {
            switch ($key) {
                case 'name':
                    $category['name'] = (string) $category['name'];
                    if (empty($category['name'])) {
                        throw $this->createServiceException("名称不能为空，保存栏目失败");
                    }
                    break;
                case 'code':
                    if (empty($category['code'])) {
                        throw $this->createServiceException("编码不能为空，保存栏目失败");
                    } else {
                        if (!preg_match("/^[a-zA-Z0-9_]+$/i", $category['code'])) {
                            throw $this->createServiceException("编码({$category['code']})含有非法字符，保存栏目失败");
                        }
                        if (ctype_digit($category['code'])) {
                            throw $this->createServiceException("编码({$category['code']})不能全为数字，保存栏目失败");
                        }
                        $exclude = empty($releatedCategory['code']) ? null : $releatedCategory['code'];
                        if (!$this->isCategoryCodeAvaliable($category['code'], $exclude)) {
                            throw $this->createServiceException("编码({$category['code']})不可用，保存栏目失败");
                        }
                    }
                    break;

                case 'parentId':
                    $category['parentId'] = (int) $category['parentId'];
                    if ($category['parentId'] > 0) {
                        $parentCategory = $this->getCategory($category['parentId']);
                        if (empty($parentCategory)) {
                            throw $this->createServiceException("父栏目(ID:{$category['parentId']})不存在，保存栏目失败");
                        }
                    }
                    break;
            }
        }

        return $category;
    }

    public function findCategoriesCountByParentId($parentId){
       return $this->getCategoryDao()->findCategoriesCountByParentId($parentId);
    }

    private function getCategoryDao ()
    {
        return $this->createDao('Article.CategoryDao');
    }

    private function getLogService()
    {
        return $this->createService('System.LogService');
    }

}