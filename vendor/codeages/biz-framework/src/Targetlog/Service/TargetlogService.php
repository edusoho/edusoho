<?php

namespace Codeages\Biz\Framework\Targetlog\Service;

interface TargetlogService
{
    const DEBUG = 1;
    const INFO = 2;
    const NOTICE = 3;
    const WARNING = 4;
    const ERROR = 5;
    const CRITICAL = 6;
    const ALERT = 7;
    const EMERGENCY = 8;

    public function log($level, $targetType, $targetId, $message, array $context = array());

    public function getLog($id);

    public function searchLogs($conditions, $orderBy, $start, $limit);

    public function countLogs($conditions);
}
