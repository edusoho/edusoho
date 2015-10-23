<?php
namespace Topxia\Service\CloudPlatform\Dao;

interface AppLogDao 
{
    public function getLog($id);

    public function getLastLogByCodeAndToVersion($code, $toVersion);

    public function findLogs($start, $limit);

    public function findLogCount();

    public function addLog($log);
}