<?php

namespace Biz\User\Service;

interface UserActiveService
{
    public function analysisActiveUser($startTime, $endTime);

    public function saveOnline($onLine);
}
