<?php
namespace Topxia\Service\RefererLog;

interface RefererLogService
{
    public function addRefererLog($refererlog);

    public function getRefererLogById($id);

    public function searchAnalysisRefererLogSum($conditions, $groupBy);

    public function searchAnalysisRefererLogs($conditions, $groupBy);

}
