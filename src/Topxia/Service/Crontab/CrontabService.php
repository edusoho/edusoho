<?php
namespace Topxia\Service\Crontab;

interface CrontabService
{
    public function getJob($id);

    public function createJob($task);

    public function executeJob($id);

    public function deleteJob($id);

    public function scheduleJobs();

    public function getNextExcutedTime();

    public function setNextExcutedTime($nextExcutedTime);
}