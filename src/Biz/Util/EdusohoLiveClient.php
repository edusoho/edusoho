<?php

namespace Biz\Util;

use Biz\CloudPlatform\CloudAPIFactory;
use AppBundle\Common\ArrayToolkit;

class EdusohoLiveClient
{
    const LIVE_STATUS_UNSTART = 'unstart';
    const LIVE_STATUS_LIVING = 'live';
    const LIVE_STATUS_PAUSE = 'pause';
    const LIVE_STATUS_CLOSED = 'closed';
    const OLD_ES_LIVE_PROVIDER = 8;
    const NEW_ES_LIVE_PROVIDER = 9;
    const LIVE_ROOM_LARGE = 'large';
    const LIVE_ROOM_SMALL = 'small';

    private $cloudApi;

    /**
     * 创建直播.
     *
     * @param array $args 直播参数，支持的参数有：title, speaker, startTime, endTime, authUrl, jumpUrl, errorJumpUrl, roomType
     *
     * @return [type] [description]
     */
    public function createLive(array $args)
    {
        return $this->createCloudApi('root')->post('/lives', $args);
    }

    public function updateLive(array $args)
    {
        return $this->createCloudApi('root')->patch('/lives/'.$args['liveId'], $args);
    }

    public function getCapacity()
    {
        $args = array(
            'timestamp' => time().'',
        );

        return $this->createCloudApi('leaf')->get('/lives/capacity', $args);
    }

    public function getRoomUrl($args, $server = 'leaf')
    {
        return $this->createCloudApi($server)->post('/lives/'.$args['liveId'].'/room_url', $args);
    }

    public function deleteLive($liveId)
    {
        $args = array(
            'liveId' => $liveId,
        );

        return $this->createCloudApi('root')->delete('/lives/'.$liveId, $args);
    }

    public function getMaxOnline($liveId)
    {
        $args = array(
            'liveId' => $liveId,
        );

        return $this->createCloudApi('leaf')->get('/lives/'.$liveId.'/max_online', $args);
    }

    public function entryLive($args)
    {
        return $this->createCloudApi('leaf')->post('/lives/'.$args['liveId'].'/entry_room', $args);
    }

    public function entryReplay($args, $server = 'leaf')
    {
        return $this->createCloudApi($server)->post('/lives/'.$args['liveId'].'/record_url', $args);
    }

    public function createReplayList($liveId, $title, $provider)
    {
        $args = array(
            'liveId' => $liveId,
            'title' => $title,
            'provider' => $provider,
        );

        return $this->createCloudApi('root')->post('/lives/'.$liveId.'/records', $args);
    }

    public function isAvailableRecord($liveId, $server = 'root')
    {
        $args = array(
            'liveId' => $liveId,
        );

        $response = $this->createCloudApi($server)->get('/lives/'.$liveId.'/available_record', $args);

        return isset($response['success']) ? true : false;
    }

    public function setLiveLogo($logoData)
    {
        $filter = array(
            'logoPcUrl',
            'logoClientUrl',
            'logoGotoUrl',
        );

        $logoData = ArrayToolkit::parts($logoData, $filter);

        return $this->createCloudApi('root')->post('/liveaccount/logo/set', $logoData);
    }

    /**
     * check live status
     *
     * @param [type] $lives array(liveProvider => array(liveId,liveId,...),...)
     *
     * @return array array(liveId => 'status',...) status：unstart|live|pause|closed
     */
    public function checkLiveStatus($lives)
    {
        $args = array('liveIds' => $lives);

        return $this->createCloudApi('leaf')->get('/lives/rooms_status', $args);
    }

    public function getLiveAccount()
    {
        return $this->createCloudApi('root')->get('/lives/account');
    }

    public function getLiveOverview()
    {
        return $this->createCloudApi('root')->get('/me/live/overview');
    }

    public static function isEsLive($liveProvider)
    {
        return in_array($liveProvider, array(self::OLD_ES_LIVE_PROVIDER, self::NEW_ES_LIVE_PROVIDER));
    }

    public function getLiveRoomCheckinList($liveId)
    {
        return $this->createCloudApi('leaf')->get("/lives/{$liveId}/checkin_list");
    }

    public function getLiveRoomHistory($liveId)
    {
        return $this->createCloudApi('leaf')->get("/lives/{$liveId}/history");
    }

    protected function createCloudApi($server)
    {
        if (empty($this->cloudApi[$server])) {
            $this->cloudApi[$server] = CloudAPIFactory::create($server);
        }

        return $this->cloudApi[$server];
    }

    /**
     * 仅给单元测试mock用。
     */
    public function setCloudApi($cloudApi, $server)
    {
        $this->cloudApi[$server] = $cloudApi;
    }
}
