<?php

namespace Biz\QuestionBank\Service;

interface MemberService
{
    public function findMembersByBankId($bankId);

    public function createMember($fields);

    public function batchDeleteByBankId($bankId);

    public function batchCreateMembers($bankId, $userIds);
}
