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
            ActivityException::NOTFOUND_ACTIVITY();
        }

        if ($activity['mediaType'] != 'live') {
            LiveActivityException::NOTFOUND_LIVE();
        }

        return $this->getLiveClient()->getLiveRoomRealTimeInfo($activity['ext']['liveId']);
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