<?php

namespace Biz\Article\Service\Impl;

use Biz\Article\CategoryException;
use Biz\Article\Dao\CategoryDao;
use Biz\Article\Service\CategoryService;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\System\Service\LogService;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\TreeToolkit;
use AppBundle\Common\Tree;

class CategoryServiceImpl extends BaseService implements CategoryService
{
    public function getCategory($id)
    {
        return $this->getCategoryDao()->get($id);
    }

    public function getCategoryByCode($code)
    {
        return $this->getCategoryDao()->findByCode($code);
    }

    public function getCategoryTree($isPublished = false)
    {
        $prepare = function ($categories) {
            $prepared = array();

            foreach ($categories as $category) {
                if (!isset($prepared[$category['parentId']])) {
                    $prepared[$category['parentId']] = array();
                }

                $prepared[$category['parentId']][] = $category;
            }

            return $prepared;
        };

        if ($isPublished) {
            $categories = $this->getCategoryDao()->search(array('published' => 1), null, 0, PHP_INT_MAX);
        } else {
            $categories = $this->findAllCategories();
        }

        $categories = $prepare($categories);
        $tree = array();
        $this->makeCategoryTree($tree, $categories, 0);

        return $tree;
    }

    public function getCategoryStructureTree()
    {
        return TreeToolkit::makeTree($this->getCategoryTree(), 'weight');
    }

    public function sortCategories($ids)
    {
        foreach ($ids as $index => $id) {
            $this->getCategoryDao()->update($id, array('weight' => $index + 1));
        }
    }

    protected function makeCategoryTree(&$tree, &$categories, $parentId)
    {
        static $depth = 0;

        if (isset($categories[$parentId]) && is_array($categories[$parentId])) {
            foreach ($categories[$parentId] as $category) {
                ++$depth;
                $category['depth'] = $depth;
                $tree[] = $category;
                $this->makeCategoryTree($tree, $categories, $category['id']);
                --$depth;
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
        return ArrayToolkit::index($this->getCategoryDao()->findByIds($ids), 'id');
    }

    public function findAllCategories()
    {
        return $this->getCategoryDao()->findAll();
    }

    public function isCategoryCodeAvailable($code, $exclude = null)
    {
        if (empty($code)) {
            return false;
        }

        if ($code == $exclude) {
            return true;
        }

        $category = $this->getCategoryDao()->findByCode($code);

        return $category ? false : true;
    }

    public function getCategoryByParentId($parentId)
    {
        return $this->getCategoryDao()->getByParentId($parentId);
    }

    public function findAllCategoriesByParentId($parentId)
    {
        return ArrayToolkit::index($this->getCategoryDao()->findByParentId($parentId), 'id');
    }

    public function findAllPublishedCategoriesByParentId($parentId)
    {
        return ArrayToolkit::index($this->getCategoryDao()->findAllPublishedByParentId($parentId), 'id');
    }

    public function findCategoryBreadcrumbs($categoryId)
    {
        $breadcrumbs = array();

        $categoryTree = $this->getCategoryTree();

        $indexedCategories = ArrayToolkit::index($categoryTree, 'id');

        while (true) {
            if (empty($indexedCategories[$categoryId])) {
                break;
            }

            $category = $indexedCategories[$categoryId];
            $breadcrumbs[] = $category;

            if (empty($category['parentId'])) {
                break;
            }

            $categoryId = $category['parentId'];
        }

        return array_reverse($breadcrumbs);
    }

    public function createCategory(array $category)
    {
        $category = ArrayToolkit::parts($category, array('name', 'code', 'parentId', 'publishArticle', 'seoTitle', 'seoKeyword', 'seoDesc', 'published'));

        if (!ArrayToolkit::requireds($category, array('name', 'code', 'parentId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $this->_filterCategoryFields($category);

        $category['createdTime'] = time();

        $category = $this->getCategoryDao()->create($category);

        return $category;
    }

    public function updateCategory($id, array $fields)
    {
        $category = $this->getCategory($id);

        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        $fields = ArrayToolkit::parts($fields, array('name', 'code', 'weight', 'parentId', 'publishArticle', 'seoTitle', 'seoKeyword', 'seoDesc', 'published'));

        if (empty($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $this->_filterCategoryFields($fields);

        $category = $this->getCategoryDao()->update($id, $fields);

        return $category;
    }

    public function deleteCategory($id)
    {
        $category = $this->getCategory($id);

        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        $ids = $this->findCategoryChildrenIds($id);
        $ids[] = $id;

        foreach ($ids as $id) {
            $this->getCategoryDao()->delete($id);
        }
    }

    protected function _filterCategoryFields($fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'name' => '',
            'code' => '',
            'weight' => 0,
            'publishArticle' => '',
            'seoTitle' => '',
            'seoDesc' => '',
            'published' => 1,
            'parentId' => 0,
        ));

        if (empty($fields['name'])) {
            $this->createNewException(CategoryException::EMPTY_NAME());
        }

        if (empty($fields['code'])) {
            $this->createNewException(CategoryException::EMPTY_CODE());
        } else {
            if (!preg_match('/^[a-zA-Z0-9_]+$/i', $fields['code'])) {
                $this->createNewException(CategoryException::CODE_INVALID());
            }

            if (ctype_digit($fields['code'])) {
                $this->createNewException(CategoryException::CODE_NUMERIC_INVALID());
            }
        }

        return $fields;
    }

    public function makeNavCategories($code)
    {
        $rootCategories = $this->findAllPublishedCategoriesByParentId(0);

        if (empty($code)) {
            return array($rootCategories, array(), array());
        } else {
            $category = $this->getCategoryByCode($code);
            $parentId = $category['id'];
            $categories = array();
            $activeIds = array();
            $activeIds[] = $category['id'];
            $level = 1;

            while ($parentId) {
                $activeIds[] = $parentId;
                $sibling = $this->findAllPublishedCategoriesByParentId($parentId);

                if ($sibling) {
                    $categories[$level] = $sibling;
                    ++$level;
                }

                $parent = $this->getCategory($parentId);
                $parentId = $parent['parentId'];
            }

            $categories = array_reverse($categories);

            return array($rootCategories, $categories, $activeIds);
        }
    }

    public function findCategoriesCountByParentId($parentId)
    {
        return $this->getCategoryDao()->findByParentId($parentId);
    }

    public function findCategoryTreeIds($parentId = 0, $isPublished = true)
    {
        $conditions = array();
        if ($isPublished) {
            $conditions['published'] = 1;
        }

        $categories = $this->getCategoryDao()->search($conditions, array(), 0, PHP_INT_MAX);

        $dataTree = Tree::buildWithArray($categories, $parentId);

        return $dataTree->column('id');
    }

    /**
     * @return CategoryDao
     */
    protected function getCategoryDao()
    {
        return $this->createDao('Article:CategoryDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
