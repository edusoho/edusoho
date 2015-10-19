<?php


namespace Custom\Service\CrontabJob\Dao;


interface CrontabJobDao
{
    public function getJobByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId);
}