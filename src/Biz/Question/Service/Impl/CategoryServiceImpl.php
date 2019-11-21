<?php

namespace Biz\Question\Service\Impl;

use Biz\BaseService;
use Biz\Question\Service\CategoryService;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\TreeToolkit;
use Biz\QuestionBank\QuestionBankException;
use Biz\Common\CommonException;
use Biz\Taxonomy\CategoryException;

class CategoryServiceImpl extends BaseService implements CategoryService
{
    public function getCategory($id)
    {
        return $this->getCategoryDao()->get($id);
    }

    public function getCategoryStructureTree($bankId)
    {
        return TreeToolkit::makeTree($this->getCategoryTree($bankId), 'weight');
    }

    public function findCategoryChildrenIds($id)
    {
        $category = $this->getCategory($id);

        if (empty($category)) {
            return array();
        }

        $tree = $this->getCategoryTree($category['bankId']);

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

    public function getCategoryTree($bankId)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBank($bankId);

        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        $categories = $this->findCategories($bankId);
        $categories = ArrayToolkit::group($categories, 'parentId');

        $tree = array();
        $this->makeCategoryTree($tree, $categories, 0);

        return $tree;
    }

    public function findCategories($bankId)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBank($bankId);

        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        return $this->getCategoryDao()->findByBankId($bankId);
    }

    public function batchCreateCategory($bankId, $parentId, $names)
    {
        if (empty($names)) {
            return array();
        }

        $questionBank = $this->getQuestionBankService()->getQuestionBank($bankId);

        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        if (!$this->getQuestionBankService()->validateCanManageBank($bankId)) {
            $this->createAccessDeniedException();
        }

        $categories = array();
        $user = $this->getCurrentUser();
        foreach ($names as $name) {
            $categories[] = array(
                'bankId' => $bankId,
                'name' => $name,
                'parentId' => $parentId,
                'userId' => $user['id'],
            );
        }

        return $this->getCategoryDao()->batchCreate($categories);
    }

    public function updateCategory($id, $fields)
    {
        $category = $this->getCategory($id);

        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        if (!$this->getQuestionBankService()->validateCanManageBank($category['bankId'])) {
            $this->createAccessDeniedException();
        }

        $fields = ArrayToolkit::parts($fields, array('name'));

        if (empty($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        return $this->getCategoryDao()->update($id, $fields);
    }

    public function deleteCategory($id)
    {
        $category = $this->getCategory($id);

        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        if (!$this->getQuestionBankService()->validateCanManageBank($category['bankId'])) {
            $this->createAccessDeniedException();
        }

        try {
            $this->beginTransaction();

            $ids = $this->findCategoryChildrenIds($id);
            $ids[] = $id;

            foreach ($ids as $id) {
                $this->getCategoryDao()->delete($id);
            }

            $questions = $this->getQuestionService()->findQuestionsByCategoryIds($ids);
            if (!empty($questions)) {
                $this->getQuestionService()->batchUpdateCategoryId(ArrayToolkit::column($questions, 'id'), 0);
            }

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
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

    protected function getCategoryDao()
    {
        return $this->createDao('Question:CategoryDao');
    }

    protected function getQuestionDao()
    {
        return $this->createDao('Question:QuestionDao');
    }

    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }
}
