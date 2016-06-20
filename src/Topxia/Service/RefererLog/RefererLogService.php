<?php
namespace Topxia\Service\RefererLog;

interface ReferLogService
{
    public function addRefererLog($referLog);

    public function findRefererLogById($id);

}
