<?php

namespace Biz\System\Service;

interface H5SettingService
{
    public function getDiscovery($portal, $mode = 'published', $usage = 'show');

    public function filter($discoverySettings, $usage = 'show');

    public function getCourseCondition($portal, $mode = 'published');
}
