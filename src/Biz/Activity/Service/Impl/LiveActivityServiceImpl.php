<?php

namespace Biz\Activity\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\AthenaLiveToolkit;
use Biz\Activity\Dao\LiveActivityDao;
use Biz\Activity\Service\LiveActivityService;
use Biz\AppLoggerConstant;
use Biz\BaseService;
use Biz\Course\Service\LiveReplayService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Biz\User\Service\UserService;
use Biz\Util\EdusohoLiveClient;
use Topxia\Service\Common\ServiceKernel;

class LiveActivityServiceImpl extends BaseService implements LiveActivityService
{
    private $client;

    public function getLiveActivity($id)
    {
        return $this->getLiveActivityDao()->get($id);
    }

    public function findLiveActivitiesByIds($ids)
    {
        return $this->getLiveActivityDao()->findByIds($ids);
    }

    public function createLiveActivity($activity, $ignoreValidation = false)
    {
        if (!$ignoreValidation && (empty($activity['startTime'])
                || $activity['startTime'] <= time()
                || empty($activity['length'])
                || $activity['length'] <= 0)
        ) {
            throw $this->createInvalidArgumentException('开始时间或直播时长有误');
        }

        //创建直播室
        if (empty($activity['startTime'])
            || $activity['startTime'] <= time()
        ) {
            //此时不创建直播教室
            $live = array(
                'id' => 0,
                'provider' => 0,
            );
        } else {
            $live = $this->createLiveroom($activity);
        }

        if (empty($live)) {
            throw $this->createNotFoundException('云直播创建失败，请重试！');
        }

        if (isset($live['error'])) {
            $error = '帐号已过期' == $live['error'] ? '直播服务已过期' : $live['error'];
            throw $this->createServiceException($error);
        }

        $activity['liveId'] = $live['id'];
        $activity['liveProvider'] = $live['provider'];

        $liveActivity = array(
            'liveId' => $live['id'],
            'liveProvider' => $live['provider'],
        );
        $liveActivity['roomCreated'] = $live['id'] > 0 ? 1 : 0;

        return $this->getLiveActivityDao()->create($liveActivity);
    }

    public function updateLiveActivity($id, &$fields, $activity)
    {
        $preLiveActivity = $liveActivity = $this->getLiveActivityDao()->get($id);

        if (empty($liveActivity)) {
            return array();
        }
        $fields = array_merge($activity, $fields);
        if (!$liveActivity['roomCreated']) {
            if ($fields['startTime'] > time()) {
                $live = $this->createLiveroom($fields);
                $liveActivity['liveId'] = $live['id'];
                $liveActivity['liveProvider'] = $live['provider'];
                $liveActivity['roomCreated'] = 1;

                $liveActivity = $this->getLiveActivityDao()->update($id, $liveActivity);
            }
        } elseif ($fields['endTime'] > time()) {
            //直播还未结束的情况下才更新直播房间信息
            $liveParams = array(
                'liveId' => $liveActivity['liveId'],
                'summary' => empty($fields['remark']) ? '' : $fields['remark'],
                'title' => $fields['title'],
            );
            //直播开始后不更新开始时间和直播时长
            if ($fields['startTime'] > time()) {
                $liveParams['startTime'] = $fields['startTime'];
                $liveParams['endTime'] = (string) ($fields['startTime'] + $fields['length'] * 60);
            }

            $this->getEdusohoLiveClient()->updateLive($liveParams);
        }

        $live = ArrayToolkit::parts($fields, array('replayStatus', 'fileId'));

        if (!empty($live['fileId'])) {
            $live['mediaId'] = $live['fileId'];
            $live['replayStatus'] = LiveReplayService::REPLAY_VIDEO_GENERATE_STATUS;
        }

        unset($live['fileId']);

        if (!empty($live)) {
            $liveActivity = $this->getLiveActivityDao()->update($id, $live);
        }

        $this->getLogService()->info(AppLoggerConstant::LIVE, 'update_live_activity', "修改直播活动（#{$activity['id']}, #{$liveActivity['id']}）", array(
            'preActivity' => $activity,
            'preLiveActivity' => $preLiveActivity,
            'newLiveActivity' => $liveActivity,
        ));

        return $liveActivity;
    }

    public function updateLiveStatus($id, $status)
    {
        $liveActivity = $this->getLiveActivityDao()->get($id);
        if (empty($liveActivity)) {
            return;
        }

        if (!in_array($status, array(EdusohoLiveClient::LIVE_STATUS_LIVING, EdusohoLiveClient::LIVE_STATUS_CLOSED, EdusohoLiveClient::LIVE_STATUS_PAUSE))) {
            throw $this->createInvalidArgumentException('Argument invalid');
        }

        $update = $this->getLiveActivityDao()->update($liveActivity['id'], array('progressStatus' => $status));
        $this->getLogService()->info(AppLoggerConstant::LIVE, 'update_live_status', "修改直播进行状态，由‘{$liveActivity['progressStatus']}’改为‘{$status}’", array('preLiveActivity' => $liveActivity, 'newLiveActivity' => $update));

        return $update;
    }

    public function deleteLiveActivity($id)
    {
        //删除直播室
        $liveActivity = $this->getLiveActivityDao()->get($id);
        if (empty($liveActivity)) {
            return;
        }

        $this->getLiveActivityDao()->delete($id);
        if (!empty($liveActivity['liveId'])) {
            $this->getEdusohoLiveClient()->deleteLive($liveActivity['liveId'], $liveActivity['liveProvider']);
            $this->getLogService()->info(AppLoggerConstant::LIVE, 'delete_live_activity', "删除直播活动（#{$liveActivity['id']}）", $liveActivity);
        }
    }

    public function search($conditions, $orderbys, $start, $limit)
    {
        return $this->getLiveActivityDao()->search($conditions, $orderbys, $start, $limit);
    }

    /**
     * @return LiveActivityDao
     */
    protected function getLiveActivityDao()
    {
        return $this->createDao('Activity:LiveActivityDao');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->biz->service('User:UserService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->biz->service('System:SettingService');
    }

    public function getEdusohoLiveClient()
    {
        if (empty($this->client)) {
            $this->client = new EdusohoLiveClient();
        }

        return $this->client;
    }

    /**
     * @param  $activity
     *
     * @throws \Codeages\Biz\Framework\Service\Exception\NotFoundException
     *
     * @return array
     */
    public function createLiveroom($activity)
    {
        $speaker = $this->getUserService()->getUser($activity['fromUserId']);
        if (empty($speaker)) {
            throw $this->createNotFoundException('教师不存在！');
        }
        $speaker = $speaker['nickname'];

        $liveLogo = $this->getSettingService()->get('course');
        $liveLogoUrl = '';
        $baseUrl = $this->biz['env']['base_url'];
        if (!empty($liveLogo) && array_key_exists('live_logo', $liveLogo) && !empty($liveLogo['live_logo'])) {
            $liveLogoUrl = $baseUrl.'/'.$liveLogo['live_logo'];
        }
        $callbackUrl = $this->buildCallbackUrl($activity);

        $live = $this->getEdusohoLiveClient()->createLive(array(
            'summary' => empty($activity['remark']) ? '' : $activity['remark'],
            'title' => $activity['title'],
            'speaker' => $speaker,
            'startTime' => $activity['startTime'].'',
            'endTime' => ($activity['startTime'] + $activity['length'] * 60).'',
            'authUrl' => $baseUrl.'/live/auth',
            'jumpUrl' => $baseUrl.'/live/jump?id='.$activity['fromCourseId'],
            'liveLogoUrl' => $liveLogoUrl,
            'callback' => $callbackUrl,
        ));

        return $live;
    }

    protected function buildCallbackUrl($activity)
    {
        $baseUrl = $this->biz['env']['base_url'];

        $duration = $activity['startTime'] + $activity['length'] * 60 + 86400 - time();
        $args = array('duration' => $duration, 'data' => array(
            'courseId' => $activity['fromCourseId'],
            'type' => 'course',
        ));
        $token = $this->getTokenService()->makeToken('live.callback', $args);

        return AthenaLiveToolkit::generateCallback($baseUrl, $token['token'], $activity['fromCourseId']);
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }
}
