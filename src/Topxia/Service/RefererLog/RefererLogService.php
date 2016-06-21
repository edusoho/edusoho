<?php
namespace Topxia\Service\RefererLog;

interface ReferLogService
{
    public function addRefererLog($targertId, $targertType, $refererUrl);

    public function findRefererLogById($id);

}
