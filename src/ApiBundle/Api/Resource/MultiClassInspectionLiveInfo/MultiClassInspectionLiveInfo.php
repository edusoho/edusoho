<?php

namespace ApiBundle\Api\Resource\MultiClassInspectionLiveInfo;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Activity\ActivityException;
use Biz\Activity\LiveActivityException;
use Biz\Activity\Service\ActivityService;
use Biz\Util\EdusohoLiveClient;

class MultiClassInspectionLiveInfo extends AbstractResource
{
    public function get(ApiRequest $request, $activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId, true);
        if (!$activity) {
            throw ActivityException::NOTFOUND_ACTIVITY();
        }

        if ('live' != $activity['mediaType']) {
            throw LiveActivityException::NOTFOUND_LIVE();
        }

        if (EdusohoLiveClient::SELF_ES_LIVE_PROVIDER != $activity['ext']['liveProvider']) {
            throw LiveActivityException::LIVE_PROVIDER_NOT_SUPPORT();
        }

        try {
            $info = $this->getLiveClient()->getLiveRoomRealTimeInfo($activity['ext']['liveId']);
            if ($info['base']['startTime'] == time() && 'unstart' == $info['info']['status']) {
                $info['info']['status'] = 'ontOnTime';
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return $info;
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->biz->service('Activity:ActivityService');
    }

    /**
     * @return EdusohoLiveClient
     */
    protected function getLiveClient()
    {
        return $this->biz['educloud.live_client'];
    }
}
