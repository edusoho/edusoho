<?php

namespace Biz\QuestionBank\Service;

interface MemberService
{
    public function findMembersByBankId($bankId);

    public function getMemberByBankIdAndUserId($bankId, $userId);

    public function createMember($fields);

    public function batchDeleteByBankId($bankId);

    public function batchCreateMembers($bankId, $userIds);

    public function findMembersByUserId($userId);

    public function resetBankMembers($bankId, $members);

    public function isMemberInBank($bankId, $userId);
}
