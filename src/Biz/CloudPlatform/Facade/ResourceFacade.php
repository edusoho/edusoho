<?php

namespace Biz\CloudPlatform\Facade;

interface ResourceFacade
{
    /**
     * 资源播放
     */
    public function makePlayToken($file, $lifetime = 600, $payload = []);
}
