<?php

namespace Topxia\Service\Util;

use \RuntimeException;
use Topxia\Service\CloudPlatform\Client\CloudApi;

class EdusohoLiveClient
{
    protected $cloudApi;

    public function __construct (array $options)
    {
        $this->cloudApi = new CloudApi($options);
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

    public function getCapacity()
    {
        $args = array(
            'timestamp' => time() . ''
        );
        return $this->cloudApi->get('/lives/capacity', $args);
    }

    public function startLive($params)
    {
        return $this->cloudApi->post('/lives/'.$params['liveId'].'/room_url', $params);
    }

    public function deleteLive($liveId)
    {   
        $args = array(
            "liveId" => $liveId, 
            "provider" => "vhall"
        );
        return $this->cloudApi->delete('/lives/'.$liveId, $args);
    }

    public function entryLive($liveId, $params)
    {
        $params = array(
            "liveId" => $liveId,
            "role" => "student",
            "provider" => "vhall"
        );
        return $this->cloudApi->post('/lives/'.$liveId.'/room_url', $params);
    }

    public function entryReplay($liveId, $replayId)
    {
        $args = array(
            'liveId' => $liveId,
            'replayId' => $replayId,
            "provider" => "vhall"
        );
        return $this->cloudApi->post('/lives/'.$liveId.'/record_url', $args);
    }

    public function createReplayList($liveId, $title)
    {
        $args = array(
            "liveId" => $liveId, 
            "title" => $title,
            "provider" => "vhall"
        );
        $replayList = $this->cloudApi->post('/lives/'.$liveId.'/records', $args);
        $replayList = json_decode($replayList['data'],true);
        return $replayList;
    }

}