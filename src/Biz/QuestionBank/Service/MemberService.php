<?php

namespace Biz\QuestionBank\Service;

use Biz\System\Annotation\Log;

interface MemberService
{
    public function findMembersByBankId($bankId);

    public function getMemberByBankIdAndUserId($bankId, $userId);

    public function createMember($fields);

    public function batchDeleteByBankId($bankId);

    public function batchCreateMembers($bankId, $userIds);

    public function findMembersByUserId($userId);

    /**
     * @param $bankId
     * @param $members
     *
     * @return mixed
     * @Log(module="question_bank",action="update_teacher",serviceName="QuestionBank:QuestionBankService",funcName="getQuestionBank",param="bankId")
     */
    public function resetBankMembers($bankId, $members);

    public function isMemberInBank($bankId, $userId);
}
