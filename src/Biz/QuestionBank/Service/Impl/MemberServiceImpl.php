<?php

namespace Biz\QuestionBank\Service\Impl;

use Biz\BaseService;
use Biz\QuestionBank\Service\MemberService;
use Biz\Common\CommonException;
use AppBundle\Common\ArrayToolkit;

class MemberServiceImpl extends BaseService implements MemberService
{
    public function findMembersByBankId($bankId)
    {
        return $this->getMemberDao()->findByBankId($bankId);
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

    protected function getMemberDao()
    {
        return $this->createDao('QuestionBank:MemberDao');
    }
}
