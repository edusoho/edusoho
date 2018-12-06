<?php

namespace Biz\Taxonomy\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\Taxonomy\CategoryException;
use Biz\Taxonomy\Service\CategoryService;
use Biz\Taxonomy\Dao\CategoryDao;
use Biz\Taxonomy\Dao\CategoryGroupDao;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\TreeToolkit;

class CategoryServiceImpl extends BaseService implements CategoryService
{
    public function findCategoriesByGroupIdAndParentId($groupId, $parentId)
    {
        if (!isset($groupId) || !isset($parentId) || $groupId < 0 || $parentId < 0) {
            return array();
        }

        return $this->getCategoryDao()->findByGroupIdAndParentId($groupId, $parentId);
    }

    public function getCategory($id)
    {
        if (empty($id)) {
            return null;
        }

        return $this->getCategoryDao()->get($id);
    }

    public function getCategoryByCode($code)
    {
        return $this->getCategoryDao()->getByCode($code);
    }

    public function getCategoryTree($groupId)
    {
        $group = $this->getGroup($groupId);

        if (empty($group)) {
            $this->createNewException(CategoryException::NOTFOUND_GROUP());
        }

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
        $data = $this->findCategories($groupId);
        $categories = $prepare($data);

        $tree = array();
        $this->makeCategoryTree($tree, $categories, 0);

        return $tree;
    }

    public function getCategoryStructureTree($groupId)
    {
        return TreeToolkit::makeTree($this->getCategoryTree($groupId), 'weight');
    }

    public function sortCategories($ids)
    {
        foreach ($ids as $index => $id) {
            $this->updateCategory($id, array('weight' => $index + 1));
        }
    }

    public function findCategories($groupId)
    {
        $group = $this->getGroup($groupId);

        if (empty($group)) {
            $this->createNewException(CategoryException::NOTFOUND_GROUP());
        }

        $magic = $this->getSettingService()->get('magic');

        if (isset($magic['enable_org']) && $magic['enable_org']) {
            $user = $this->getCurrentUser();
            $orgId = !empty($user['org']) ? $user['org']['id'] : null;

            return $this->getCategoryDao()->findByGroupIdAndOrgId($group['id'], $orgId);
        } else {
            return $this->getCategoryDao()->findByGroupId($group['id']);
        }
    }

    public function findAllCategoriesByParentId($parentId)
    {
        return ArrayToolkit::index($this->getCategoryDao()->findAllByParentId($parentId), 'id');
    }

    public function findGroupRootCategories($groupCode)
    {
        $group = $this->getGroupByCode($groupCode);

        if (empty($group)) {
            $this->createNewException(CategoryException::NOTFOUND_GROUP());
        }

        return $this->getCategoryDao()->findByGroupIdAndParentId($group['id'], 0);
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

    public function findCategoryBreadcrumbs($categoryId)
    {
        $breadcrumbs = array();
        $category = $this->getCategory($categoryId);

        if (empty($category)) {
            return array();
        }

        $categoryTree = $this->getCategoryTree($category['groupId']);

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

    public function makeNavCategories($code, $groupCode)
    {
        $rootCagoies = $this->findGroupRootCategories($groupCode);

        if (empty($code)) {
            return array($rootCagoies, array(), array());
        } else {
            $category = $this->getCategoryByCode($code);
            $parentId = $category['id'];
            $categories = array();
            $activeIds = array();
            $activeIds[] = $category['id'];
            $level = 1;

            while ($parentId) {
                $activeIds[] = $parentId;
                $sibling = $this->findAllCategoriesByParentId($parentId);

                if ($sibling) {
                    $categories[$level] = $sibling;
                    ++$level;
                }

                $parent = $this->getCategory($parentId);
                $parentId = $parent['parentId'];
            }

            //翻转会重建key索引
            $categories = array_reverse($categories);

            return array($rootCagoies, $categories, $activeIds);
        }
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

        $category = $this->getCategoryDao()->getByCode($code);

        return $category ? false : true;
    }

    public function createCategory(array $category)
    {
        $category = ArrayToolkit::parts($category, array('description', 'name', 'code', 'groupId', 'parentId', 'icon'));

        if (!ArrayToolkit::requireds($category, array('name', 'code', 'groupId', 'parentId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $this->filterCategoryFields($category);

        $category = $this->setCategoryOrg($category);
        $category = $this->getCategoryDao()->create($category);

        return $category;
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

    public function updateCategory($id, array $fields)
    {
        $category = $this->getCategory($id);

        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        $fields = ArrayToolkit::parts($fields, array('description', 'name', 'code', 'weight', 'parentId', 'icon'));

        if (empty($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        // filterCategoryFields里有个判断，需要用到这个$fields['groupId']
        $fields['groupId'] = $category['groupId'];

        $this->filterCategoryFields($fields, $category);

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

    public function getGroup($id)
    {
        return $this->getGroupDao()->get($id);
    }

    public function getGroupByCode($code)
    {
        return $this->getGroupDao()->getByCode($code);
    }

    public function getGroups($start, $limit)
    {
        return $this->getGroupDao()->find($start, $limit);
    }

    public function findAllGroups()
    {
        return $this->getGroupDao()->findAll();
    }

    public function addGroup(array $group)
    {
        return $this->getGroupDao()->create($group);
    }

    public function deleteGroup($id)
    {
        return $this->getGroupDao()->delete($id);
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

    protected function filterCategoryFields(&$category, $relatedCategory = null)
    {
        foreach (array_keys($category) as $key) {
            switch ($key) {
                case 'name':
                    $category['name'] = (string) $category['name'];

                    if (empty($category['name'])) {
                        $this->createNewException(CategoryException::EMPTY_NAME());
                    }

                    break;
                case 'code':
                    if (empty($category['code'])) {
                        $this->createNewException(CategoryException::EMPTY_CODE());
                    } else {
                        if (!preg_match('/^[a-zA-Z0-9_]+$/i', $category['code'])) {
                            $this->createNewException(CategoryException::CODE_INVALID());
                        }

                        if (ctype_digit($category['code'])) {
                            $this->createNewException(CategoryException::CODE_DIGIT_INVALID());
                        }

                        $exclude = empty($relatedCategory['code']) ? null : $relatedCategory['code'];
                        if (!$this->isCategoryCodeAvailable($category['code'], $exclude)) {
                            $this->createNewException(CategoryException::CODE_UNAVAILABLE());
                        }
                    }

                    break;
                case 'groupId':
                    $category['groupId'] = (int) $category['groupId'];
                    $group = $this->getGroup($category['groupId']);

                    if (empty($group)) {
                        $this->createNewException(CategoryException::NOTFOUND_GROUP());
                    }

                    break;
                case 'parentId':
                    $category['parentId'] = (int) $category['parentId'];

                    if ($category['parentId'] > 0) {
                        $parentCategory = $this->getCategory($category['parentId']);

                        if (empty($parentCategory) || $parentCategory['groupId'] != $category['groupId']) {
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
        return $this->createDao('Taxonomy:CategoryDao');
    }

    /**
     * @return CategoryGroupDao
     */
    protected function getGroupDao()
    {
        return $this->createDao('Taxonomy:CategoryGroupDao');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }
}
