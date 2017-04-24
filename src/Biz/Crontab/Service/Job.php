<?php

namespace Biz\Crontab\Service;

interface Job
{
    public function execute($params);
}
