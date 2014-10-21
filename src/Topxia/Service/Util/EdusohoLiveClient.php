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

    public function startLive($liveId)
    {
        $args = array(
            "liveId" => $liveId, 
        );
        return $this->cloudApi->post('/lives/'.$liveId.'/room_url', $args);
    }

    public function deleteLive($liveId)
    {   
        $args = array(
            "liveId" => $liveId, 
        );
        return $this->cloudApi->delete('/lives/'.$liveId, $args);
    }

    public function entryLive($liveId, $params)
    {
        $params['liveId'] = $liveId;
        $params['role'] = 'student';
        return $this->cloudApi->post('/lives/'.$liveId.'/room_url', $params);
    }

    public function getCapacity()
    {
        $args = array(
            'timestamp' => time() . '',
        );
        return $this->cloudApi->get('/lives/capacity', $args);
    }

    public function entryReplay($liveId, $replayId)
    {
        $args = array(
            'liveId' => $liveId,
            'replayId' => $replayId
        );
        return $this->cloudApi->post('/lives/'.$liveId.'/record_url', $args);
    }

    public function createReplayList($liveId, $title)
    {
        $args = array(
            "liveId" => $liveId, 
            "title" => $title
        );
        $replayList = $this->cloudApi->post('/lives/'.$liveId.'/records', $args);
        $replayList = json_decode($replayList['data'],true);
        return $replayList;
    }

}