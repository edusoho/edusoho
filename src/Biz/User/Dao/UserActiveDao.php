<?php
namespace Biz\User\Dao;

interface UserActiveDao
{
    public function createActiveUser($userId);

    public function getActiveUser($userId);

    public function analysisActiveUser($startTime, $endTime);

}
