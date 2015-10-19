<?php


namespace Custom\Service\CrontabJob\Impl;


use Custom\Service\CrontabJob\CrontabJobService;
use Topxia\Service\Crontab\Impl\CrontabServiceImpl as BaseServiceImpl;

class CrontabJobServiceImpl extends BaseServiceImpl implements CrontabJobService
{
    public function getJobByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId)
    {
        return $this->getJobDao()->getJobByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId);
    }

    public function updateJob($id, $fields)
    {

        $job = $this->getJobDao()->updateJob($id, $fields);
        $this->refreshNextExecutedTime();
        return $job;
    }

}