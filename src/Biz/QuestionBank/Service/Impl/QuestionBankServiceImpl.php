<?php

namespace Biz\QuestionBank\Service\Impl;

use Biz\BaseService;
use Biz\QuestionBank\Dao\QuestionBankDao;
use Biz\QuestionBank\Service\CategoryService;
use Biz\QuestionBank\Service\QuestionBankService;
use AppBundle\Common\ArrayToolkit;
use Biz\Taxonomy\CategoryException;
use Biz\Common\CommonException;
use Biz\QuestionBank\QuestionBankException;

class QuestionBankServiceImpl extends BaseService implements QuestionBankService
{
    public function getQuestionBank($id)
    {
        return $this->getQuestionBankDao()->get($id);
    }

    public function searchQuestionBanks($conditions, $orderBys, $start, $limit, $columns = array())
    {
        $conditions = $this->prepareConditions($conditions);

        return $this->getQuestionBankDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function countQuestionBanks($conditions)
    {
        $conditions = $this->prepareConditions($conditions);

        return $this->getQuestionBankDao()->count($conditions);
    }

    public function createQuestionBank($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('name', 'categoryId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $category = $this->getCategoryService()->getCategory($fields['categoryId']);
        if (empty($category)) {
            $this->createNewException(CategoryException::NOTFOUND_CATEGORY());
        }

        try {
            $this->beginTransaction();
            $questionBank = array(
                'name' => $fields['name'],
                'categoryId' => $fields['categoryId'],
            );
            $questionBank = $this->fillOrgId($questionBank);
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

        return $questionBank;
    }

    public function updateQuestionBank($id, $fields)
    {
        $fields = ArrayToolkit::parts($fields, array('name', 'categoryId', 'members'));
        if (empty($fields)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

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

            $updateFields = array(
                'name' => empty($fields['name']) ? $questionBank['name'] : $fields['name'],
                'categoryId' => empty($fields['categoryId']) ? $questionBank['categoryId'] : $fields['categoryId'],
            );
            $updateFields = $this->fillOrgId($updateFields);
            $newQuestionBank = $this->getQuestionBankDao()->update($id, $updateFields);
            if (!empty($fields['categoryId']) && $fields['categoryId'] != $questionBank['categoryId']) {
                $this->getCategoryService()->waveCategoryBankNum($fields['categoryId'], 1);
                $this->getCategoryService()->waveCategoryBankNum($questionBank['categoryId'], -1);
            }

            $this->getMemberService()->batchDeleteByBankId($newQuestionBank['id']);
            if (!empty($fields['members'])) {
                $members = explode(',', $fields['members']);
                $this->getMemberService()->batchCreateMembers($newQuestionBank['id'], $members);
            }

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }

        return $newQuestionBank;
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

            $this->getCategoryService()->waveCategoryBankNum($questionBank['categoryId'], -1);

            $this->getMemberService()->batchDeleteByBankId($questionBank['id']);

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    public function findAllQuestionBanks()
    {
        return $this->getQuestionBankDao()->findAll();
    }

    public function validateCanManageBank($bankId, $permission = 'admin_question_bank')
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->hasPermission($permission)) {
            return true;
        }

        $members = $this->getMemberService()->findMembersByBankId($bankId);
        $memberUserIds = ArrayToolkit::column($members, 'userId');
        if (in_array($user['id'], $memberUserIds)) {
            return true;
        }

        return false;
    }

    public function waveTestpaperNum($id, $diff)
    {
        return $this->getQuestionBankDao()->wave(array($id), array('testpaperNum' => $diff));
    }

    public function waveQuestionNum($id, $diff)
    {
        return $this->getQuestionBankDao()->wave(array($id), array('questionNum' => $diff));
    }

    protected function prepareConditions($conditions)
    {
        if (isset($conditions['categoryId'])) {
            $conditions['categoryIds'] = array();
            if (!empty($conditions['categoryId'])) {
                $childrenIds = $this->getCategoryService()->findAllChildrenIdsByParentId($conditions['categoryId']);
                $conditions['categoryIds'] = array_merge(array($conditions['categoryId']), $childrenIds);
            }
            unset($conditions['categoryId']);
        }

        return $conditions;
    }

    /**
     * @return QuestionBankDao
     */
    protected function getQuestionBankDao()
    {
        return $this->createDao('QuestionBank:QuestionBankDao');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('QuestionBank:CategoryService');
    }

    protected function getMemberService()
    {
        return $this->createService('QuestionBank:MemberService');
    }
}
