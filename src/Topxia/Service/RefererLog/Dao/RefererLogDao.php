<?php
namespace Topxia\Service\RefererLog\Dao;

interface RefererLogDao
{
    public function addRefererLog($referLog);

    public function getRefererLogById($id);

    public function waveRefererLog($id, $field, $diff);

    public function searchRefererLogs($conditions, $orderBy, $start, $limit);

    public function searchRefererLogCount($conditions);

    public function searchRefererLogsGroupByTargetId($conditions, $orderBy, $start, $limit);

    public function searchRefererLogCountGroupByTargetId($conditions);
}
