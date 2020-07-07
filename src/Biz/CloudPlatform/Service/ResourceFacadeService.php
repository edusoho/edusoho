<?php

namespace Biz\CloudPlatform\Service;

interface ResourceFacadeService
{
    /**
     * 资源播放
     */
    public function makePlayToken($file, $lifetime = 600, $payload = []);

    public function agentInWhiteList($userAgent);

    public function getFrontPlaySDKPathByType($type);
}
