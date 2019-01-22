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

        $args = array(
            'start_time' => 0,
            'end_time' => time(),
            'target' => UserMarketingActivitySynclogService::TARGET_MOBILE,
            'target_value' => $mobile,
        );
        $activities = $this->getRemoteActivitiesByArgs($args);

        foreach ($activities as $activitiy) {
            $this->syncActivityByUser($user, $activitiy);
        }

        $this->getUserMarketingActivitySynclogService()->createSyncLog(array(
            'args' => $args,
            'data' => $activities,
            'target' => UserMarketingActivitySynclogService::TARGET_MOBILE,
            'target_value' => $mobile,
            'rangeStartTime' => $args['start_time'],
            'rangeEndTime' => $args['end_time'],
        ));
    }

    public function syncAll()
    {
        $lastSyncLog = $this->getUserMarketingActivitySynclogService()->getLastSyncLogByTargetAndTargetValue(UserMarketingActivitySynclogService::TARGET_ALL, '0');
        $args = array(
            'start_time' => empty($lastSyncLog) ? 0 : $lastSyncLog['rangeEndTime'],
            'end_time' => time(),
            'target' => UserMarketingActivitySynclogService::TARGET_ALL,
            'target_value' => '0',
        );
        $activities = $this->getRemoteActivitiesByArgs($args);

        foreach ($activities as $activitiy) {
            $user = $this->getUserService()->getUserByVerifiedMobile($activitiy['mobile']);
            if (empty($user)) {
                continue;
            }
            $this->syncActivityByUser($user, $activitiy);
        }

        $this->getUserMarketingActivitySynclogService()->createSyncLog(array(
            'args' => $args,
            'data' => $activities,
            'target' => UserMarketingActivitySynclogService::TARGET_ALL,
            'target_value' => '0',
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

    protected function syncActivityByUser($user, $activitiy)
    {
        $oldActivitiy = $this->findByJoinedIdAndType($activitiy['joinedId'], $activitiy['type']);
        if (empty($oldActivitiy)) {
            $this->getUserMarketingActivityDao()->create(array(
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
            ));
        } else {
            $this->getUserMarketingActivityDao()->update($oldActivitiy['id'], array(
                'status' => $activitiy['status'],
                'price' => $activitiy['price'],
            ));
        }
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
