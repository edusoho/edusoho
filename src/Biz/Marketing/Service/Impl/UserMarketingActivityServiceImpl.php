<?php

namespace Biz\Marketing\Service\Impl;

use Biz\Marketing\Service\UserMarketingActivityService;
use Biz\BaseService;
use Biz\Marketing\Dao\UserMarketingActivityDao;
use Biz\Marketing\Service\MarketingPlatformService;
use Biz\User\Service\UserService;
use Biz\Marketing\Service\UserMarketingActivitySynclogService;
use Biz\Marketing\MarketingAPIFactory;
use Biz\User\UserException;
use AppBundle\Common\ArrayToolkit;

class UserMarketingActivityServiceImpl extends BaseService implements UserMarketingActivityService
{
    public function searchActivities($conditions, $orderBy, $start, $limit)
    {
        return $this->getUserMarketingActivityDao()->search($conditions, $orderBy, $start, $limit);
    }

    public function searchActivityCount($conditions)
    {
        return $this->getUserMarketingActivityDao()->count($conditions);
    }

    public function syncByMobile($mobile)
    {
        $user = $this->getUserService()->getUserByVerifiedMobile($mobile);
        if (empty($user)) {
            throw UserException::NOTFOUND_USER();
        }

        $lastSyncLog = $this->getUserMarketingActivitySynclogService()->getLastSyncLogByTargetAndTargetValue(UserMarketingActivitySynclogService::TARGET_MOBILE, $mobile);
        if (empty($lastSyncLog)) {
            $startTime = 0;
        } elseif (empty($lastSyncLog['data'])) {
            $startTime = $lastSyncLog['rangeStartTime'];
        } else {
            $startTime = $lastSyncLog['rangeEndTime'];
        }

        $args = array(
            'start_time' => $startTime,
            'end_time' => time(),
            'target' => UserMarketingActivitySynclogService::TARGET_MOBILE,
            'target_value' => $mobile,
        );
        $activities = $this->getRemoteActivitiesByArgs($args);

        $createActivities = array();
        $updateActivities = array();

        foreach ($activities as $activitiy) {
            $oldActivitiy = $this->findByJoinedIdAndType($activitiy['joinedId'], $activitiy['type']);
            if (empty($oldActivitiy)) {
                $createActivities[] = array(
                    'userId' => $user['id'],
                    'mobile' => $activitiy['mobile'],
                    'activityId' => $activitiy['activityId'],
                    'joinedId' => $activitiy['joinedId'],
                    'name' => $activitiy['name'],
                    'type' => $activitiy['type'],
                    'status' => $activitiy['status'],
                    'cover' => $activitiy['cover'],
                    'itemType' => $activitiy['itemType'],
                    'itemSourceId' => $activitiy['itemSourceId'],
                    'originPrice' => $activitiy['originPrice'],
                    'price' => $activitiy['price'],
                    'joinedTime' => $activitiy['joinedTime'],
                );
            } else {
                $updateActivities[] = array(
                    'id' => $oldActivitiy['id'],
                    'status' => $activitiy['status'],
                    'price' => $activitiy['price'],
                    'updatedTime' => time(),
                );
            }
        }

        if (!empty($createActivities)) {
            $this->getUserMarketingActivityDao()->batchCreate($createActivities);
        }

        if (!empty($updateActivities)) {
            $this->getUserMarketingActivityDao()->batchUpdate(ArrayToolkit::column($updateActivities, 'id'), $updateActivities);
        }

        $this->getUserMarketingActivitySynclogService()->createSyncLog(array(
            'args' => $args,
            'data' => $activities,
            'target' => UserMarketingActivitySynclogService::TARGET_MOBILE,
            'targetValue' => $mobile,
            'rangeStartTime' => $args['start_time'],
            'rangeEndTime' => $args['end_time'],
        ));
    }

    public function findByJoinedIdAndType($joinedId, $type)
    {
        return $this->getUserMarketingActivityDao()->findByJoinedIdAndType($joinedId, $type);
    }

    protected function getRemoteActivitiesByArgs($args)
    {
        try {
            $systemUser = $this->getUserService()->getUserByType('system');
            $this->getMarketingPlatformService()->simpleLogin($systemUser['id']);
            $client = MarketingAPIFactory::create();
            $activities = $client->get(
                '/activities_sync',
                $args,
                array('MERCHANT-USER-ID: '.$systemUser['id'])
            );
        } catch (\Exception $e) {
            $activities = array();
        }

        return $activities;
    }

    /**
     * @return UserMarketingActivityDao
     */
    protected function getUserMarketingActivityDao()
    {
        return $this->createDao('Marketing:UserMarketingActivityDao');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createDao('User:UserService');
    }

    /**
     * @return MarketingPlatformService
     */
    protected function getMarketingPlatformService()
    {
        return $this->createService('Marketing:MarketingPlatformService');
    }

    /**
     * @return UserMarketingActivitySynclogService
     */
    protected function getUserMarketingActivitySynclogService()
    {
        return $this->createService('Marketing:UserMarketingActivitySynclogService');
    }
}
