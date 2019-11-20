<?php

namespace Biz\QuestionBank\Service;

interface QuestionBankService
{
    public function getQuestionBank($id);

    public function searchQuestionBanks($conditions, $orderBys, $start, $limit, $columns = array());

    public function countQuestionBanks($conditions);

    public function createQuestionBank($fields);

    public function updateQuestionBank($id, $fields);

    public function deleteQuestionBank($id);

    public function validateCanManageBank($bankId, $permission = 'admin_question_bank');
}
