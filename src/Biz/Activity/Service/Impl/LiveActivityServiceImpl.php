<?php

namespace Biz\Activity\Service\Impl;

use Biz\BaseService;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Util\EdusohoLiveClient;
use Biz\Activity\Service\LiveActivityService;

class LiveActivityServiceImpl extends BaseService implements LiveActivityService
{
    private $client;

    public function getLiveActivity($id)
    {
        return $this->getLiveActivityDao()->get($id);
    }

    public function createLiveActivity($activity)
    {
        //创建直播室
        $speaker = $this->getUserService()->getUser($activity['fromUserId']);
        if (empty($speaker)) {
            throw $this->createNotFoundException($this->getServiceKernel()->trans('教师不存在！'));
        }

        $speaker = $speaker['nickname'];

        $liveLogo    = $this->getSettingService()->get('course');
        $liveLogoUrl = "";

        if (!empty($liveLogo) && array_key_exists("live_logo", $liveLogo) && !empty($liveLogo["live_logo"])) {
            $liveLogoUrl = $this->getServiceKernel()->getEnvVariable('baseUrl')."/".$liveLogo["live_logo"];
        }

        $live = $this->getEdusohoLiveClient()->createLive(array(
            'summary'     => $activity['remark'],
            'title'       => $activity['title'],
            'speaker'     => $speaker,
            'startTime'   => $activity['startTime'].'',
            'endTime'     => ($activity['startTime'] + $activity['length'] * 60).'',
            'authUrl'     => $activity['_base_url'].'/live/auth',
            'jumpUrl'     => $activity['_base_url'].'/live/jump?id='.$activity['fromCourseId'],
            'liveLogoUrl' => $liveLogoUrl
        ));

        if (empty($live)) {
            throw $this->createNotFoundException($this->getServiceKernel()->trans('云直播创建失败，请重试！'));
        }

        if (isset($live['error'])) {
            throw $this->createServiceException($live['error']);
        }

        $activity['liveId']       = $live['id'];
        $activity['liveProvider'] = $live['provider'];

        $liveActivity = array(
            'liveId'       => $live['id'],
            'liveProvider' => $live['provider']
        );
        return $this->getLiveActivityDao()->create($liveActivity);
    }

    public function updateLiveActivity($id, $fields)
    {
        $liveActivity = $this->getLiveActivityDao()->get($id);
        $liveParams   = array(
            'liveId'   => $liveActivity['liveId'],
            'provider' => $liveActivity['liveProvider'],
            'summary'  => $fields['remark'],
            'title'    => $fields['title'],
            'authUrl'  => $fields['_base_url'].'/live/auth',
            'jumpUrl'  => $fields['_base_url'].'/live/jump?id='.$fields['fromCourseId']
        );

        if (array_key_exists('startTime', $fields)) {
            $liveParams['startTime'] = $fields['startTime'];
        }

        if (array_key_exists('startTime', $fields) && array_key_exists('length', $fields)) {
            $liveParams['endTime'] = ($fields['startTime'] + $fields['length'] * 60).'';
        }

        $this->getEdusohoLiveClient()->updateLive($liveParams);
        //live activity自身没有需要更新的信息
        $fields['id'] = $id;
        return $fields;
    }

    public function deleteLiveActivity($id)
    {
        $liveActivity = $this->getLiveActivityDao()->get($id);
        if (empty($liveActivity)) {
            return;
        }

        $this->getLiveActivityDao()->delete($id);
        $result = $this->getEdusohoLiveClient()->deleteLive($liveActivity['liveId'], $liveActivity['liveProvider']);
    }

    protected function getLiveActivityDao()
    {
        return $this->createDao('Activity:LiveActivityDao');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    public function getEdusohoLiveClient()
    {
        if (empty($this->client)) {
            $this->client = new EdusohoLiveClient();
        }
        return $this->client;
    }
}
