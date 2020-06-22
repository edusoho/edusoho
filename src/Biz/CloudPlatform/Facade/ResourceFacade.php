<?php
namespace Biz\CloudPlatform\Facade;

interface ResourceFacade
{
    /**
     * 资源播放
     */

    public function makePlayToken($no, $lifetime = 600, $payload = array());
}