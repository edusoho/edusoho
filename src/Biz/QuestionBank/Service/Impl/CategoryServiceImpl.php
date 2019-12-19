<?php

namespace Biz\QuestionBank\Service\Impl;

use Biz\BaseService;
use Biz\QuestionBank\Dao\CategoryDao;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\QuestionBankException;
use AppBundle\Common\TreeToolkit;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\System\Service\SettingService;
use Biz\Taxonomy\CategoryException;
use Biz\QuestionBank\Service\MemberService;
use Biz\QuestionBank\Service\QuestionBankService;

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

    public function getCategoryStructureTree($rootId = 0)
    {
        return TreeToolkit::makeTree($this->getCategoryTree($rootId), 'weight');
    }

    public function createCategory(array $category)
    {
        if (!ArrayToolkit::requireds($category, array('name', 'parentId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $category = ArrayToolkit::parts($category, array('name', 'parentId'));

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

        $this->filterCategoryFields($fields);

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

        $this->canDelete($children);

        $this->getCategoryDao()->batchDelete(array('ids' => ArrayToolkit::column($children, 'id')));
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

    public function getCategoryTree($rootId = 0)
    {
        $categories = $this->findAllCategories();
        $categories = ArrayToolkit::group($categories, 'parentId');

        $tree = array();
        $this->makeCategoryTree($tree, $categories, $rootId);

        return $tree;
    }

    public function getCategoryAndBankMixedTree()
    {
        $banks = $this->getQuestionBankService()->findUserManageBanks();
        if (empty($banks)) {
            return array();
        }

        $categories = $this->findAllCategories();
        if (empty($categories)) {
            return $banks;
        }

        $categories = ArrayToolkit::group($categories, 'parentId');
        $banks = ArrayToolkit::group($banks, 'categoryId');
        $tree = array();
        $this->makeCategoryAndBankMixedTree($banks, $tree, $categories, 0);

        return $tree;
    }

    public function findAllCategories()
    {
        if ($this->getSettingService()->node('magic.enable_org')) {
            return $this->getCategoryDao()->findByPrefixOrgCode($this->getCurrentUser()->getSelectOrgCode());
        }

        return $this->getCategoryDao()->findAll();
    }

    public function findAllCategoriesByParentId($parentId)
    {
        return ArrayToolkit::index($this->getCategoryDao()->findAllByParentId($parentId), 'id');
    }

    public function canDelete($categories)
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

    protected function makeCategoryAndBankMixedTree($banks, &$tree, &$categories, $parentId)
    {
        static $treeDepth = 0;

        if (!empty($banks[$parentId])) {
            ++$treeDepth;
            foreach ($banks[$parentId] as &$bank) {
                $bank['depth'] = $treeDepth;
            }
            $tree = array_merge($tree, $banks[$parentId]);
            --$treeDepth;
        }

        if (isset($categories[$parentId]) && is_array($categories[$parentId])) {
            foreach ($categories[$parentId] as $category) {
                ++$treeDepth;
                $category['depth'] = $treeDepth;
                $tree[] = $category;
                $this->makeCategoryAndBankMixedTree($banks, $tree, $categories, $category['id']);
                --$treeDepth;
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

        $currentOrg = $this->getCurrentUser()->getCurrentOrg();

        if (empty($category['parentId'])) {
            if (empty($currentOrg)) {
                return $category;
            }
            $category['orgId'] = $currentOrg['id'];
            $category['orgCode'] = $currentOrg['orgCode'];
        } else {
            $parentCategory = $this->getCategory($category['parentId']);
            $category['orgId'] = $parentCategory['orgId'];
            $category['orgCode'] = $parentCategory['orgCode'];
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

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('QuestionBank:MemberService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }
}
