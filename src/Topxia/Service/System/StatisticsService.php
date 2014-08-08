<?php 

namespace Topxia\Service\System;

interface StatisticsService
{
	public function getOnlineCount($retentionTime);
}