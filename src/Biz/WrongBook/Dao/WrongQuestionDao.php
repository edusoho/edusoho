<?php

namespace Biz\WrongBook\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface WrongQuestionDao extends AdvancedDaoInterface
{
    public function searchWrongQuestionsWithCollect($conditions, $orderBys, $start, $limit, $columns);

    public function countWrongQuestionWithCollect($conditions);

    public function searchWrongQuestionsWithDistinctItem($conditions, $orderBys, $start, $limit, $columns);

    public function findWrongQuestionBySceneIds($items);
}
