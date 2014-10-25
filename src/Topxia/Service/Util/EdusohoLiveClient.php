<?php

namespace Topxia\Service\Util;

use \RuntimeException;
use Topxia\Service\CloudPlatform\Client\CloudAPI;

class EdusohoLiveClient
{
    protected $cloudApi;

    public function __construct (array $options)
    {
        $this->cloudApi = new CloudAPI($options);
    }

    /**
     * 创建直播
     *
     * @param  array  $args 直播参数，支持的参数有：title, speaker, startTime, endTime, authUrl, jumpUrl, errorJumpUrl
     * @return [type]       [description]
     */
    public function createLive(array $args)
    {
        return $this->cloudApi->post('/lives', $args);
    }

    public function updateLive(array $args)
    {
        return $this->cloudApi->patch('/lives/'.$args['liveId'], $args);
    }

    public function getCapacity()
    {
        $args = array(
            'timestamp' => time() . ''
        );
        return $this->cloudApi->get('/lives/capacity', $args);
    }

    public function startLive($args)
    {
        return $this->cloudApi->post('/lives/'.$args['liveId'].'/room_url', $args);
    }

    public function deleteLive($liveId, $provider)
    {   
        $args = array(
            "liveId" => $liveId, 
            "provider" => $provider
        );
        return $this->cloudApi->delete('/lives/'.$liveId, $args);
    }

    public function entryLive($args)
    {
        return $this->cloudApi->post('/lives/'.$args['liveId'].'/room_url', $args);
    }

    public function entryReplay($args)
    {
        return $this->cloudApi->post('/lives/'.$args['liveId'].'/record_url', $args);
    }

    public function createReplayList($liveId, $title, $provider)
    {
        $args = array(
            "liveId" => $liveId, 
            "title" => $title,
            "provider" => $provider
        );
        return $this->cloudApi->post('/lives/'.$liveId.'/records', $args);
    }

}