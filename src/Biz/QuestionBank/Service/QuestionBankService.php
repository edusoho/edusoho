<?php

namespace Biz\QuestionBank\Service;

interface QuestionBankService
{
    public function getQuestionBank($id);

    public function searchQuestionBanks($conditions, $orderBys, $start, $limit, $columns = array());

    public function countQuestionBanks($conditions);
}
