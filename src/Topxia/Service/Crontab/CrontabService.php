<?php
namespace Topxia\Service\Crontab;

interface CrontabService
{
    public function getJob($id);

    public function createJob($task);

    public function executeJob($id);

    public function deleteJob($id);

    public function deleteJobs($targetId, $targetType);

    public function scheduleJobs();

    public function getNextExcutedTime();

    public function setNextExcutedTime($nextExcutedTime);

    public function findJobByTargetTypeAndTargetId($targetType, $targetId);

    public function findJobByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId);

    public function updateJob($id, $fields);

    public function searchJobs($conditions, $sort, $start, $limit);

    public function searchJobsCount($conditions);
}
