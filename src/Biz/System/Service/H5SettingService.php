<?php

namespace Biz\System\Service;

interface H5SettingService
{
    public function getDiscovery($portal, $mode);

    public function getCourseCondition($portal, $mode);
}
