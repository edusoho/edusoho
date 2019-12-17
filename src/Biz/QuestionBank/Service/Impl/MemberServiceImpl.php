<?php

namespace Biz\QuestionBank\Service\Impl;

use Biz\BaseService;
use Biz\QuestionBank\Dao\MemberDao;
use Biz\QuestionBank\Service\MemberService;
use Biz\Common\CommonException;
use AppBundle\Common\ArrayToolkit;
use Biz\QuestionBank\Service\QuestionBankService;

class MemberServiceImpl extends BaseService implements MemberService
{
    public function findMembersByBankId($bankId)
    {
        return $this->getMemberDao()->findByBankId($bankId);
    }

    public function getMemberByBankIdAndUserId($bankId, $userId)
    {
        return $this->getMemberDao()->getByBankIdAndUserId($bankId, $userId);
    }

    public function createMember($fields)
    {
        if (!ArrayToolkit::requireds($fields, array('bankId', 'userId'))) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        return $this->getMemberDao()->create($fields);
    }

    public function batchDeleteByBankId($bankId)
    {
        if (empty($bankId)) {
            return;
        }

        return $this->getMemberDao()->batchDelete(array('bankId' => $bankId));
    }

    public function findMembersByUserId($userId)
    {
        return $this->getMemberDao()->findByUserId($userId);
    }

    public function batchCreateMembers($bankId, $userIds)
    {
        if (empty($userIds)) {
            return;
        }

        $members = array();
        foreach ($userIds as $userId) {
            $members[] = array(
                'bankId' => $bankId,
                'userId' => $userId,
            );
        }

        return $this->getMemberDao()->batchCreate($members);
    }

    public function resetBankMembers($bankId, $members)
    {
        $this->batchDeleteByBankId($bankId);
        if (!empty($members)) {
            $createMembers = explode(',', $members);
            $this->batchCreateMembers($bankId, $createMembers);
        }
    }

    public function isMemberInBank($bankId, $userId)
    {
        $questionBank = $this->getQuestionBankService()->getQuestionBank($bankId);
        if (empty($questionBank)) {
            return false;
        }

        $member = $this->getMemberByBankIdAndUserId($bankId, $userId);
        if (empty($member)) {
            return false;
        }

        return true;
    }

    /**
     * @return MemberDao
     */
    protected function getMemberDao()
    {
        return $this->createDao('QuestionBank:MemberDao');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }
}
