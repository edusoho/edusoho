<?php

namespace Biz\WrongBook\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface WrongQuestionDao extends AdvancedDaoInterface
{
    public function searchWrongQuestionsWithCollect($conditions, $orderBys, $start, $limit, $columns);
}
