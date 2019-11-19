<?php

namespace Biz\QuestionBank\Service\Impl;

use Biz\BaseService;
use Biz\QuestionBank\Dao\CategoryDao;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\QuestionBankException;
use AppBundle\Common\TreeToolkit;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\Taxonomy\CategoryException;

class CategoryServiceImpl extends BaseService implements CategoryService
{
    public function getCategory($id)
    {
        return $this->getCategoryDao()->get($id);
    }

    public function findCategoriesByIds($ids)
    {
        $categories = $this->getCategoryDao()->findByIds($ids);

        return ArrayToolkit::index($categories, 'id');
    }

    public function getCategoryStructureTree()
    {
        return TreeToolkit::makeTree($this->getCategoryTree(), 'weight');
    }

    public function createCategory(array $category)
    {
        $category = ArrayToolkit::parts($category, array('name', 'parentId'));

        if (!ArrayToolkit::requireds($category, array('name', 'parentId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $this->filterCategoryFields($category);
        $category = $this->setCategoryOrg($category);

        return $this->getCategoryDao()->create($category);
    }

    public function updateCategory($id, array $fields)
    {
        $category = $this->getCategory($id);

        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        $fields = ArrayToolkit::parts($fields, array('name', 'parentId', 'weight'));

        if (empty($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $this->filterCategoryFields($fields, $category);

        return $this->getCategoryDao()->update($id, $fields);
    }

    public function deleteCategory($id)
    {
        $category = $this->getCategory($id);

        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        $children = $this->findCategoryChildren($id);
        $children[] = $category;

        $this->validateCategoriesCanDelete($children);

        foreach ($children as $category) {
            $this->getCategoryDao()->delete($category['id']);
        }
    }

    public function waveCategoryBankNum($id, $diff)
    {
        return $this->getCategoryDao()->wave(array($id), array('bankNum' => $diff));
    }

    public function findCategoryChildren($id)
    {
        $category = $this->getCategory($id);

        if (empty($category)) {
            return array();
        }

        $tree = $this->getCategoryTree();

        $children = array();
        $depth = 0;

        foreach ($tree as $node) {
            if ($node['id'] == $category['id']) {
                $depth = $node['depth'];
                continue;
            }

            if ($depth > 0 && $depth < $node['depth']) {
                $children[] = $node;
            }

            if ($depth > 0 && $depth >= $node['depth']) {
                break;
            }
        }

        return $children;
    }

    public function getCategoryTree()
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
        $data = $this->findAllCategories();
        $categories = $prepare($data);

        $tree = array();
        $this->makeCategoryTree($tree, $categories, 0);

        return $tree;
    }

    public function findAllCategories()
    {
        return $this->getCategoryDao()->findAll();
    }

    public function findAllCategoriesByParentId($parentId)
    {
        return ArrayToolkit::index($this->getCategoryDao()->findAllByParentId($parentId), 'id');
    }

    public function validateCategoriesCanDelete($categories)
    {
        foreach ($categories as $category) {
            if ($category['bankNum'] > 0) {
                $this->createNewException(QuestionBankException::FORBIDDEN_DELETE_CATEGORY());
            }
        }

        return true;
    }

    public function findAllChildrenIdsByParentId($parentId)
    {
        // TODO: Implement findAllChildrenIdsByParentId() method.
        return ArrayToolkit::column($this->getCategoryDao()->findAllByParentId($parentId), 'id');
    }

    public function sortCategories($ids)
    {
        foreach ($ids as $index => $id) {
            $this->updateCategory($id, array('weight' => $index + 1));
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

    protected function setCategoryOrg($category)
    {
        $magic = $this->getSettingService()->get('magic');

        if (empty($magic['enable_org'])) {
            return $category;
        }

        $user = $this->getCurrentUser();
        $currentOrg = $user['org'];

        if (empty($category['parentId'])) {
            if (empty($user['org'])) {
                return $category;
            }
            $category['orgId'] = $currentOrg['id'];
            $category['orgCode'] = $currentOrg['orgCode'];
        } else {
            $parentOrg = $this->getCategory($category['parentId']);
            $category['orgId'] = $parentOrg['orgId'];
            $category['orgCode'] = $parentOrg['orgCode'];
        }

        return $category;
    }

    protected function filterCategoryFields(&$category)
    {
        foreach (array_keys($category) as $key) {
            switch ($key) {
                case 'name':
                    $category['name'] = (string) $category['name'];

                    if (empty($category['name'])) {
                        $this->createNewException(CategoryException::EMPTY_NAME());
                    }

                    break;
                case 'parentId':
                    $category['parentId'] = (int) $category['parentId'];

                    if ($category['parentId'] > 0) {
                        $parentCategory = $this->getCategory($category['parentId']);

                        if (empty($parentCategory)) {
                            $this->createNewException(CategoryException::NOTFOUND_PARENT_CATEGORY());
                        }
                    }

                    break;
            }
        }

        return $category;
    }

    /**
     * @return CategoryDao
     */
    protected function getCategoryDao()
    {
        return $this->createDao('QuestionBank:CategoryDao');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
