<?php
namespace Topxia\Service\Crontab;
use Topxia\Service\Common\ServiceKernel;

interface Job
{   
    public function execute($params);
}