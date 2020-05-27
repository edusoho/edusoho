<?php

namespace Biz\QuestionBank\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\QuestionBank\Dao\QuestionBankDao;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\MemberService;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Taxonomy\CategoryException;
use Codeages\Biz\ItemBank\ItemBank\Service\ItemBankService;

class QuestionBankServiceImpl extends BaseService implements QuestionBankService
{
    public function getQuestionBank($id)
    {
        $questionBank = $this->getQuestionBankDao()->get($id);

        return $this->wrapQuestionBank($questionBank);
    }

    public function getQuestionBankByCourseSetId($courseSetId)
    {
        $questionBank = $this->getQuestionBankDao()->getByCourseSetId($courseSetId);

        return $this->wrapQuestionBank($questionBank);
    }

    public function getQuestionBankByItemBankId($itemBankId)
    {
        $questionBank = $this->getQuestionBankDao()->getByItemBankId($itemBankId);

        return $this->wrapQuestionBank($questionBank);
    }

    public function findQuestionBanksByIds($ids)
    {
        $questionBanks = $this->getQuestionBankDao()->findByIds($ids);

        return $this->wrapQuestionBanks($questionBanks);
    }

    public function searchQuestionBanks($conditions, $orderBys, $start, $limit, $columns = [])
    {
        $conditions = $this->prepareConditions($conditions);

        $questionBanks = $this->getQuestionBankDao()->search($conditions, $orderBys, $start, $limit, $columns);

        return $this->wrapQuestionBanks($questionBanks);
    }

    public function countQuestionBanks($conditions)
    {
        $conditions = $this->prepareConditions($conditions);

        return $this->getQuestionBankDao()->count($conditions);
    }

    public function createQuestionBank($fields)
    {
        if (!ArrayToolkit::requireds($fields, ['name', 'categoryId'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $category = $this->getCategoryService()->getCategory($fields['categoryId']);
        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        try {
            $this->beginTransaction();
            $questionBank = [
                'name' => $fields['name'],
                'categoryId' => $fields['categoryId'],
                'orgCode' => $this->getCurrentUser()->getSelectOrgCode(),
            ];
            $questionBank = $this->fillOrgId($questionBank);
            $itemBank = $this->getItemBankService()->createItemBank(['name' => $questionBank['name']]);
            $questionBank['itemBankId'] = $itemBank['id'];
            $questionBank = $this->getQuestionBankDao()->create($questionBank);
            $this->getCategoryService()->waveCategoryBankNum($fields['categoryId'], 1);

            if (!empty($fields['members'])) {
                $members = explode(',', $fields['members']);
                $this->getMemberService()->batchCreateMembers($questionBank['id'], $members);
            }

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $this->wrapQuestionBank($questionBank);
    }

    public function updateQuestionBankWithMembers($id, $fields, $members)
    {
        $questionBank = $this->getQuestionBank($id);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        if (!empty($fields['categoryId'])) {
            $category = $this->getCategoryService()->getCategory($fields['categoryId']);
            if (empty($category)) {
                $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
            }
        }

        try {
            $this->beginTransaction();

            $updateFields = [
                'name' => empty($fields['name']) ? $questionBank['name'] : $fields['name'],
                'categoryId' => empty($fields['categoryId']) ? $questionBank['categoryId'] : $fields['categoryId'],
            ];
            $newQuestionBank = $this->updateQuestionBank($id, $updateFields);

            if (!empty($fields['categoryId'])) {
                $this->changeQuestionBankCategory($fields['categoryId'], $questionBank['categoryId']);
            }

            $this->getMemberService()->resetBankMembers($newQuestionBank['id'], $members);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $newQuestionBank;
    }

    public function updateQuestionBank($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, ['name', 'categoryId', 'isHidden']);
        if (empty($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        $fields = $this->fillOrgId($fields);

        $questionBank = $this->getQuestionBankDao()->update($id, $fields);
        $this->getItemBankService()->updateItemBank($questionBank['itemBankId'], ['name' => $questionBank['name']]);

        return $this->wrapQuestionBank($questionBank);
    }

    public function deleteQuestionBank($id)
    {
        $questionBank = $this->getQuestionBank($id);
        if (empty($questionBank)) {
            $this->createNewException(QuestionBankException::NOT_FOUND_BANK());
        }

        if ($questionBank['testpaperNum'] > 0 || $questionBank['questionNum'] > 0) {
            $this->createAccessDeniedException();
        }

        try {
            $this->beginTransaction();

            $this->getQuestionBankDao()->delete($id);
            $this->getItemBankService()->deleteItemBank($questionBank['itemBankId']);

            $this->getCategoryService()->waveCategoryBankNum($questionBank['categoryId'], -1);

            $this->getMemberService()->batchDeleteByBankId($questionBank['id']);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function canManageBank($bankId)
    {
        $user = $this->getCurrentUser();

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->hasPermission('admin_question_bank') || $user->hasPermission('admin_v2_question_bank')) {
            return true;
        }

        if ($this->getMemberService()->isMemberInBank($bankId, $user['id'])) {
            return true;
        }

        return false;
    }

    public function findUserManageBanks()
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return [];
        }

        if ($user->isSuperAdmin() || $user->hasPermission('admin_question_bank') || $user->hasPermission('admin_v2_question_bank')) {
            $banks = $this->getQuestionBankDao()->findAll();
            $banks = $this->wrapQuestionBanks($banks);
        } else {
            $members = $this->getMemberService()->findMembersByUserId($user['id']);
            $banks = $this->findQuestionBanksByIds(array_column($members, 'bankId'));
        }

        return $banks;
    }

    protected function wrapQuestionBank($questionBank)
    {
        $questionBank['itemBank'] = $this->getItemBankService()->getItemBank($questionBank['itemBankId']);

        return $questionBank;
    }

    protected function wrapQuestionBanks($questionBanks)
    {
        $itemBanks = $this->getItemBankService()->searchItemBanks(['ids' => array_column($questionBanks, 'itemBankId')], [], 0, PHP_INT_MAX);
        $itemBanks = ArrayToolkit::index($itemBanks, 'id');

        foreach ($questionBanks as &$questionBank) {
            $questionBank['itemBank'] = empty($itemBanks[$questionBank['itemBankId']]) ? [] : $itemBanks[$questionBank['itemBankId']];
            unset($questionBank);
        }

        return $questionBanks;
    }

    protected function changeQuestionBankCategory($newCategoryId, $oldCategoryId)
    {
        if ($newCategoryId != $oldCategoryId) {
            $this->getCategoryService()->waveCategoryBankNum($newCategoryId, 1);
            $this->getCategoryService()->waveCategoryBankNum($oldCategoryId, -1);
        }
    }

    protected function prepareConditions($conditions)
    {
        if (isset($conditions['categoryId'])) {
            $conditions['categoryIds'] = [];
            if (!empty($conditions['categoryId'])) {
                $childrenIds = $this->getCategoryService()->findAllChildrenIdsByParentId($conditions['categoryId']);
                $conditions['categoryIds'] = array_merge([$conditions['categoryId']], $childrenIds);
            }
            unset($conditions['categoryId']);
        }

        $conditions['isHidden'] = 0;

        return $conditions;
    }

    /**
     * @return QuestionBankDao
     */
    protected function getQuestionBankDao()
    {
        return $this->createDao('QuestionBank:QuestionBankDao');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('QuestionBank:CategoryService');
    }

    /**
     * @return MemberService
     */
    protected function getMemberService()
    {
        return $this->createService('QuestionBank:MemberService');
    }

    /**
     * @return ItemBankService
     */
    protected function getItemBankService()
    {
        return $this->createService('ItemBank:ItemBank:ItemBankService');
    }
}
