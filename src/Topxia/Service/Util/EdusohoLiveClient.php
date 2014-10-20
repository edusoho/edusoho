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
            'liveId' => $liveId
        );
        return $this->cloudApi->post('LiveStart', $args);
    }

    public function deleteLive($liveId)
    {
        $args = array(
            'liveId' => $liveId
        );
        return $this->cloudApi->delete('LiveDelete', $args);
    }

    public function entryLive($liveId, $params)
    {
        $url = "http://webinar.vhall.com/appaction.php?module=inituser&pid={$liveId}&email={$params['email']}&name={$params['nickname']}&k={$params['sign']}";
        return array('url' => $url);
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
        $url = "http://webinar.vhall.com/record.php?module=viewrec&id={$liveId}&rsid={$replayId}";
        return $url;
    }

    public function createReplayList($liveId, $title)
    {
        $args = array(
            "liveId" => $liveId, 
            "title" => $title
        );
        $replayList = $this->cloudApi->post('LiveReplayCreate', $args);
        if(array_key_exists("error", $replayList)){
            return $replayList;
        }
        return json_decode($replayList["data"], true);
    }

}