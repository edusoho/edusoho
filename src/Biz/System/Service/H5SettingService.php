<?php

namespace Biz\System\Service;

interface H5SettingService
{
    public function getDiscovery($portal, $mode = 'published', $usage = 'show');

    public function getDiscoveryTemplate($template, $portal, $usage = 'show');

    public function filter($discoverySettings, $portal, $usage = 'show');

    public function getCourseCondition($portal, $mode = 'published');

    /**
     * 获取app发现页版本
     * 1表示老发现页版本
     * 2表示自定义发现页版本
     *
     * @return int
     */
    public function getAppDiscoveryVersion();
}
