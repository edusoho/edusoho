<?php
namespace Mooc\Service\Article\Impl;

use Topxia\Common\ArrayToolkit;
use Mooc\Service\Article\CategoryService;
use Topxia\Service\Article\Impl\CategoryServiceImpl as BaseCategoryServiceImpl;

class CategoryServiceImpl extends BaseCategoryServiceImpl implements CategoryService
{
    public function getCategoryTreeByBranchSchoolId($branchSchoolId)
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

        $categories = $prepare($this->findCategoriesByBranchSchoolId($branchSchoolId));
        $tree       = array();
        $this->makeCategoryTree($tree, $categories, 0);

        return $tree;
    }

    public function findCategoriesByBranchSchoolId($branchSchoolId)
    {
        return $this->getCategoryDao()->findCategoriesByBranchSchoolId($branchSchoolId);
    }

    public function createCategory(array $category)
    {
        $category = ArrayToolkit::parts($category, array('name', 'code', 'weight', 'parentId', 'publishArticle', 'seoTitle', 'seoKeyword', 'seoDesc', 'published', 'branchSchoolId'));

        if (!ArrayToolkit::requireds($category, array('name', 'code', 'weight', 'parentId'))) {
            throw $this->createServiceException("缺少必要参数，，添加栏目失败");
        }

        $this->_filterCategoryFields($category);

        $category['createdTime'] = time();

        $category = $this->getCategoryDao()->addCategory($category);

        $this->getLogService()->info('category', 'create', "添加分校栏目 {$category['name']}(#{$category['id']})", $category);

        return $category;
    }

    public function updateCategory($id, array $fields)
    {
        $category = $this->getCategory($id);

        if (empty($category)) {
            throw $this->createNoteFoundException("栏目(#{$id})不存在，更新栏目失败！");
        }

        $fields = ArrayToolkit::parts($fields, array('name', 'code', 'weight', 'parentId', 'publishArticle', 'seoTitle', 'seoKeyword', 'seoDesc', 'published', 'branchSchoolId'));

        if (empty($fields)) {
            throw $this->createServiceException('参数不正确，更新栏目失败！');
        }

        $this->_filterCategoryFields($fields);

        $this->getLogService()->info('category', 'update', "编辑栏目 {$fields['name']}(#{$id})", $fields);

        return $this->getCategoryDao()->updateCategory($id, $fields);
    }

    public function makeNavCategories($code)
    {
        $rootCagoies = $this->findAllCategoriesByParentId(0);

        foreach ($rootCagoies as &$rootCagoie) {
            if ($rootCagoie['branchSchoolId'] != 0) {
                unset($rootCagoies[$rootCagoie['id']]);
            }
        }

        if (empty($code)) {
            return array($rootCagoies, array(), array());
        } else {
            $category    = $this->getCategoryByCode($code);
            $parentId    = $category['id'];
            $categories  = array();
            $activeIds   = array();
            $activeIds[] = $category['id'];
            $level       = 1;

            while ($parentId) {
                $activeIds[] = $parentId;
                $sibling     = $this->findAllCategoriesByParentId($parentId);

                if ($sibling) {
                    $categories[$level] = $sibling;
                    $level++;
                }

                $parent   = $this->getCategory($parentId);
                $parentId = $parent['parentId'];
            }

            $categories = array_reverse($categories);

            return array($rootCagoies, $categories, $activeIds);
        }
    }

    protected function _filterCategoryFields($fields)
    {
        $fields = ArrayToolkit::filter($fields, array(
            'name'           => '',
            'code'           => '',
            'weight'         => 0,
            'publishArticle' => '',
            'seoTitle'       => '',
            'seoDesc'        => '',
            'published'      => 1,
            'parentId'       => 0,
            'branchSchoolId' => 0
        ));

        if (empty($fields['name'])) {
            throw $this->createServiceException("名称不能为空，保存栏目失败");
        }

        if (empty($fields['code'])) {
            throw $this->createServiceException("编码不能为空，保存栏目失败");
        } else {
            if (!preg_match("/^[a-zA-Z0-9_]+$/i", $fields['code'])) {
                throw $this->createServiceException("编码({$fields['code']})含有非法字符，保存栏目失败");
            }

            if (ctype_digit($fields['code'])) {
                throw $this->createServiceException("编码({$fields['code']})不能全为数字，保存栏目失败");
            }
        }

        return $fields;
    }
}
