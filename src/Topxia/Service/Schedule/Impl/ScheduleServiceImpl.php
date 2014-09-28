<?php
namespace Topxia\Service\Sign\Impl;

use Topxia\Service\Common\BaseService;
use Topxia\Service\Sign\ScheduleService;
use Topxia\Service\Common\ServiceEvent;

class ScheduleServiceImpl extends BaseService implements ScheduleService
{
	public function addSchedule($schedule)
	{
		return $this->getScheduleDao()->addSchedule($schedule);
	}

	private function getScheduleDao()
	{
	    return $this->createDao('Schedule.ScheduleDao');
	}
}