<?php

namespace Biz\RefererLog\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface RefererLogDao extends GeneralDaoInterface
{
    public function findRefererLogsGroupByTargetId($targetType, $orderBy, $startTime, $endTime, $start, $limit);

    public function analysisSummary($conditions);

    public function searchAnalysisSummaryList($conditions, $groupBy, $start, $limit);

    public function countDistinctLogsByField($conditions, $field);
}
