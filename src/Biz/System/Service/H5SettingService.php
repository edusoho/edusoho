<?php

namespace Biz\System\Service;

interface H5SettingService
{
    public function getDiscovery($portal, $mode, $usage);

    public function filter($discoverySettings, $usage);

    public function getCourseCondition($portal, $mode);
}
