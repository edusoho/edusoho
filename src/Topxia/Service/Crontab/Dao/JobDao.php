<?php

namespace Topxia\Service\Crontab\Dao;

interface JobDao
{
    public function getJob($id, $lock = false);

    public function searchJobs($conditions, $orderBy, $start, $limit);

    public function searchJobsCount($conditions);

    public function addJob($job);

    public function updateJob($id, $fields);

    public function deleteJob($id);

    public function deleteJobs($targetId, $targetType);

    public function findJobByTargetTypeAndTargetId($targetType, $targetId);

    public function findJobByNameAndTargetTypeAndTargetId($jobName, $targetType, $targetId);
}
