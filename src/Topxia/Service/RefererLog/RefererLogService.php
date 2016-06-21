<?php
namespace Topxia\Service\RefererLog;

interface RefererLogService
{
    public function addRefererLog($targertId, $targertType, $refererUrl);

    public function getRefererLogById($id);

}
