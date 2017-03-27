<?php
/**
 * User: Edusoho V8
 * Date: 19/10/2016
 * Time: 19:07
 */

namespace Topxia\Service\User\Dao;


interface UserActiveDao
{
    public function createActiveUser($userId);

    public function getActiveUser($userId);

    public function analysisActiveUser($startTime, $endTime);

}