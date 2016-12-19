<?php
namespace Biz\Crontab\Service\Crontab;

interface Job
{
    public function execute($params);
}
