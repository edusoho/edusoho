<?php

namespace Biz\LiveActivity\Service\Impl;

use Biz\BaseService;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Util\EdusohoLiveClient;
use Biz\LiveActivity\Service\LiveActivityService;

class LiveActivityServiceImpl extends BaseService implements LiveActivityService
{
    public function createLiveActivity($activity)
    {
        //创建直播室
        $speaker = $this->getUserService()->getUser($this->getCurrentUser()->getId());
        $speaker = $speaker ? $speaker['nickname'] : $this->getServiceKernel()->trans('老师');

        $liveLogo    = $this->getSettingService()->get('course');
        $liveLogoUrl = "";

        if (!empty($liveLogo) && array_key_exists("live_logo", $liveLogo) && !empty($liveLogo["live_logo"])) {
            $liveLogoUrl = $this->getServiceKernel()->getEnvVariable('baseUrl')."/".$liveLogo["live_logo"];
        }

        $client = new EdusohoLiveClient();
        $live   = $client->createLive(array(
            'summary'     => $activity['remark'],
            'title'       => $activity['title'],
            'speaker'     => $speaker,
            'startTime'   => $activity['startTime'].'',
            'endTime'     => ($activity['startTime'] + $activity['length'] * 60).'',
            'authUrl'     => $activity['_base_url'].'/live/auth',
            'jumpUrl'     => $activity['_base_url'].'/live/jump?id='.$activity['fromCourseSetId'],
            'liveLogoUrl' => $liveLogoUrl
        ));

        if (empty($live)) {
            throw new \RuntimeException($this->getServiceKernel()->trans('创建直播教室失败，请重试！'));
        }

        if (isset($live['error'])) {
            throw new \RuntimeException($live['error']);
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
        // var_dump($liveActivity);
        // var_dump($fields);exit();
        $liveParams = array(
            'liveId'   => $liveActivity['liveId'],
            'provider' => $liveActivity['liveProvider'],
            'summary'  => $fields['remark'],
            'title'    => $fields['title'],
            'authUrl'  => $fields['_base_url'].'/live/auth',
            'jumpUrl'  => $fields['_base_url'].'/live/jump?id='.$fields['fromCourseSetId']
        );

        if (array_key_exists('startTime', $fields)) {
            $liveParams['startTime'] = $fields['startTime'];
        }

        if (array_key_exists('startTime', $fields) && array_key_exists('length', $fields)) {
            $liveParams['endTime'] = ($fields['startTime'] + $fields['length'] * 60).'';
        }

        $client = new EdusohoLiveClient();
        var_dump($liveParams);
        $live = $client->updateLive($liveParams);
        //live activity自身没有需要更新的信息
    }

    public function deleteLiveActivity($id)
    {
        //删除直播室
        $liveActivity = $this->getLiveActivityDao()->get($id);
        if (empty($liveActivity)) {
            return;
        }
        var_dump($liveActivity);
        $client = new EdusohoLiveClient();
        $client->deleteLive($liveActivity['liveId'], $liveActivity['liveProvider']);
        $this->getLiveActivityDao()->delete($id);
    }

    protected function getLiveActivityDao()
    {
        return $this->createDao('LiveActivity:LiveActivityDao');
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
}
