<?php

namespace Topxia\Service\Crontab\Dao;

interface JobDao
{
    public function getJob($id);

    public function searchJobs($conditions, $orderBy, $start, $limit);

    public function searchJobsCount($conditions, $orderBy, $start, $limit);

    public function addJob($task);

    public function updateJob($id, $fields);
}