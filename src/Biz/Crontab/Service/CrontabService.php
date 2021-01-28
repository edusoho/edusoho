<?php

namespace Biz\Crontab\Service;

interface CrontabService
{
    public function getNextExcutedTime();

    public function setNextExcutedTime($time);
}
