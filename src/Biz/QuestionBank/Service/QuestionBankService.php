<?php

namespace Biz\QuestionBank\Service;

interface QuestionBankService
{
    public function getQuestionBank($id);

    public function findQuestionBanksByIds($ids);

    public function searchQuestionBanks($conditions, $orderBys, $start, $limit, $columns = array());

    public function countQuestionBanks($conditions);

    public function createQuestionBank($fields);

    public function updateQuestionBankWithMembers($id, $fields, $members);

    public function updateQuestionBank($id, $fields);

    public function deleteQuestionBank($id);

    public function canManageBank($bankId, $permission = 'admin_question_bank');

    public function waveTestpaperNum($id, $diff);

    public function waveQuestionNum($id, $diff);

    public function findUserManageBanks();
}
