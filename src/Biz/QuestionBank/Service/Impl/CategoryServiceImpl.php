<?php

namespace Biz\QuestionBank\Service\Impl;

use Biz\BaseService;
use Biz\QuestionBank\Service\CategoryService;
use AppBundle\Common\TreeToolkit;
use AppBundle\Common\ArrayToolkit;

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
        return TreeToolkit::makeTree($this->getCategoryTree(), 'id');
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

        $fields = ArrayToolkit::parts($fields, array('name', 'parentId'));

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

        $ids = $this->findCategoryChildrenIds($id);
        $ids[] = $id;

        foreach ($ids as $id) {
            //todo 清除这些分类下的题库的分类
            $this->getCategoryDao()->delete($id);
        }
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

    protected function getCategoryDao()
    {
        return $this->createDao('QuestionBank:CategoryDao');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
