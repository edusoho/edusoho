<?php

namespace Biz\System\Service;

interface StatisticsService
{
    public function getOnlineCount($retentionTime);

    public function getloginCount($retentionTime);
}
