<?php

namespace Biz\Activity\Service\Impl;

use Biz\BaseService;
use Biz\Util\EdusohoLiveClient;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Topxia\Service\Common\ServiceKernel;
use Biz\Course\Service\LiveReplayService;
use Biz\Activity\Service\LiveActivityService;

class LiveActivityServiceImpl extends BaseService implements LiveActivityService
{
    private $client;

    public function getLiveActivity($id)
    {
        return $this->getLiveActivityDao()->get($id);
    }

    public function createLiveActivity($activity, $ignoreValidation = false)
    {
        if (!$ignoreValidation && (empty($activity['startTime'])
            || $activity['startTime'] <= time()
            || empty($activity['length'])
            || $activity['length'] <= 0)) {
            throw $this->createInvalidArgumentException('参数有误');
        }

        //创建直播室
        if (empty($activity['startTime'])
            || $activity['startTime'] <= time()) {
            //此时不创建直播教室
            $live = array(
                'id'       => 0,
                'provider' => 0
            );
        } else {
            $live = $this->createLiveroom($activity);
        }

        if (empty($live)) {
            throw $this->createNotFoundException('云直播创建失败，请重试！');
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
        $liveActivity['roomCreated'] = $live['id'] > 0 ? 1 : 0;
        return $this->getLiveActivityDao()->create($liveActivity);
    }

    public function updateLiveActivity($id, &$fields, $activity)
    {
        $liveActivity = $this->getLiveActivityDao()->get($id);

        if (empty($liveActivity)) {
            return array();
        }
        $fields = array_merge($liveActivity, $fields);
        if (!$liveActivity['roomCreated']) {
            if ($activity['startTime'] > time()) {
                $live                         = $this->createLiveroom($activity);
                $liveActivity['liveId']       = $live['id'];
                $liveActivity['liveProvider'] = $live['provider'];
                $liveActivity['roomCreated']  = 1;
            }
        } elseif ($activity['endTime'] > time()) {
            //直播还未结束的情况下才更新直播房间信息
            $liveParams = array(
                'liveId'  => $fields['liveId'],
                'summary' => empty($fields['remark']) ? '' : $fields['remark'],
                'title'   => $fields['title']
            );
            //直播开始后不更新开始时间和直播时长
            if ($activity['startTime'] > time()) {
                $liveParams['startTime'] = $activity['startTime'];
                $liveParams['endTime']   = (string) ($activity['startTime'] + $fields['length'] * 60);
            }

            $this->getEdusohoLiveClient()->updateLive($liveParams);
        }

        $liveActivity = ArrayToolkit::parts($fields, array('replayStatus', 'fileId'));

        if (!empty($liveActivity['fileId'])) {
            $liveActivity['mediaId']      = $liveActivity['fileId'];
            $liveActivity['replayStatus'] = LiveReplayService::REPLAY_VIDEO_GENERATE_STATUS;
            unset($liveActivity['fileId']);
        }

        if (!empty($liveActivity)) {
            $liveActivity = $this->getLiveActivityDao()->update($id, $liveActivity);
        }

        return $liveActivity;
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
        }
    }

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
     * @throws \Codeages\Biz\Framework\Service\Exception\NotFoundException
     * @return array
     */
    public function createLiveroom($activity)
    {
        $speaker = $this->getUserService()->getUser($activity['fromUserId']);
        if (empty($speaker)) {
            throw $this->createNotFoundException('教师不存在！');
        }

        $speaker = $speaker['nickname'];

        $liveLogo    = $this->getSettingService()->get('course');
        $liveLogoUrl = "";

        if (!empty($liveLogo) && array_key_exists("live_logo", $liveLogo) && !empty($liveLogo["live_logo"])) {
            $liveLogoUrl = $this->getServiceKernel()->getEnvVariable('baseUrl')."/".$liveLogo["live_logo"];
        }

        $live = $this->getEdusohoLiveClient()->createLive(array(
            'summary'     => empty($activity['remark']) ? '' : $activity['remark'],
            'title'       => $activity['title'],
            'speaker'     => $speaker,
            'startTime'   => $activity['startTime'].'',
            'endTime'     => ($activity['startTime'] + $activity['length'] * 60).'',
            //FIXME 如果上面的baseUrl和下面的$activity['_base_url']等效，则不使用下面的（因为下面的参数来自controller，应避免这种依赖）
            'authUrl'     => $activity['_base_url'].'/live/auth',
            'jumpUrl'     => $activity['_base_url'].'/live/jump?id='.$activity['fromCourseId'],
            'liveLogoUrl' => $liveLogoUrl
        ));
        return $live;
    }
}
