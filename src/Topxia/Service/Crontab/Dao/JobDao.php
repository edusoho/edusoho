<?php

namespace Topxia\Service\Crontab\Dao;

interface JobDao
{
    public function getJob($id, $lock = false);

    public function searchJobs($conditions, $orderBy, $start, $limit);

    public function searchJobsCount($conditions, $orderBy, $start, $limit);

    public function addJob($job);

    public function updateJob($id, $fields);

    public function deleteJob($id);
}