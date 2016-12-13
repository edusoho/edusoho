<?php

namespace Biz\User;

interface UserActiveService
{
    public function createActiveUser($userId);

    public function isActiveUser($userId);

    public function analysisActiveUser($startTime, $endTime);

}
