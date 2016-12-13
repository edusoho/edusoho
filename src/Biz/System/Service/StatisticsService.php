<?php

namespace Biz\System;

interface StatisticsService
{
    public function getOnlineCount($retentionTime);

    public function getloginCount($retentionTime);
}
