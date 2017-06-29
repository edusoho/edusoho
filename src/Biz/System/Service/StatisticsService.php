<?php

namespace Biz\System\Service;

interface StatisticsService
{
    public function countOnline($retentionTime);

    public function countLogin($retentionTime);
}
