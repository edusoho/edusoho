<?php
namespace Topxia\Service\RefererLog\Dao;

interface RefererLogDao
{
    public function addRefererLog($referLog);

    public function getRefererLogById($id);

    public function waveRefererLog($id, $field, $diff);

    public function findRefererLogsGroupByTargetId($targetType, $orderBy, $startTime, $endTime, $start, $limit);

    public function analysisSummary($conditions);

    public function searchAnalysisSummaryList($conditions, $groupBy, $start, $limit);

    public function countDistinctLogsByField($conditions, $field);

    public function searchRefererLogs($conditions, $orderBy, $start, $limit);

    public function searchRefererLogCount($conditions);
}
