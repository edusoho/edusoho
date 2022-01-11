<?php

namespace Biz\Live\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\LiveActivityService;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Live\Dao\LiveStatisticsDao;
use Biz\Live\LiveStatisticsProcessor\LiveStatisticsProcessorFactory;
use Biz\Live\Service\LiveStatisticsService;
use Biz\LiveStatistics\Dao\LiveMemberStatisticsDao;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\Util\EdusohoLiveClient;

class LiveStatisticsServiceImpl extends BaseService implements LiveStatisticsService
{
    public function createLiveCheckinStatistics($liveId)
    {
        $statistics = $this->generateStatisticsByLiveIdAndType($liveId, self::STATISTICS_TYPE_CHECKIN);

        return $this->getLiveStatisticsDao()->create($statistics);
    }

    public function createLiveVisitorStatistics($liveId)
    {
        $statistics = $this->generateStatisticsByLiveIdAndType($liveId, self::STATISTICS_TYPE_VISITOR);

        return $this->getLiveStatisticsDao()->create($statistics);
    }

    public function updateCheckinStatistics($liveId)
    {
        $exist = $this->getCheckinStatisticsByLiveId($liveId);

        if (empty($exist)) {
            return $this->createLiveCheckinStatistics($liveId);
        }

        $statistics = $this->generateStatisticsByLiveIdAndType($liveId, self::STATISTICS_TYPE_CHECKIN);

        return empty($statistics['data']['detail']) ? $exist : $this->getLiveStatisticsDao()->update($exist['id'], $statistics);
    }

    public function updateVisitorStatistics($liveId)
    {
        $exist = $this->getVisitorStatisticsByLiveId($liveId);

        if (empty($exist)) {
            $exist = $this->createLiveVisitorStatistics($liveId);
        } else {
            $statistics = $this->generateStatisticsByLiveIdAndType($liveId, self::STATISTICS_TYPE_VISITOR);
            $exist = $this->getLiveStatisticsDao()->update($exist['id'], $statistics);
        }
        $this->syncLiveMember($liveId, $exist);

        return $exist;
    }

    protected function syncLiveMember($liveId, $statistics)
    {
        $liveMembers = $this->getLiveMemberStatisticsDao()->search(['liveId' => $liveId], [], 0, PHP_INT_MAX, ['userId']);
        $userIds = \AppBundle\Common\ArrayToolkit::column($liveMembers, 'userId');
        $create = [];
        if (empty($statistics['data']['detail']) || (!empty($statistics['data']['sync']) && time() - $statistics['data']['syncTime'] < 2 * 3600)) {
            return;
        }
        $count = $this->getUserDao()->count([]);
        foreach ($statistics['data']['detail'] as $user) {
            $userId = $user['userId'];
            if ($userId > $count) {
                $baseUser = $this->getUserDao()->getByNickname($user['nickname']);
                if (empty($baseUser)) {
                    continue;
                }
                $userId = $baseUser['id'];
            }
            if (!empty($create[$live['liveId'].'-'.$userId]) || empty($user['firstJoin']) || in_array($userId, $userIds)) {
                continue;
            }
            $create[$liveId.'-'.$userId] = [
                'liveId' => $liveId,
                'userId' => $userId,
                'firstEnterTime' => $user['firstJoin'],
                'watchDuration' => empty($user['learnTime']) || $user['learnTime'] < 0 ? 0 : $user['learnTime'],
                'checkinNum' => 0,
                'requestTime' => time(),
                'chatNum' => 0,
                'answerNum' => 0,
            ];
        }
        if (!empty($create)) {
            $this->getLiveMemberStatisticsDao()->batchCreate(array_values($create));
        }
        $statistics['data']['sync'] = 1;
        $statistics['data']['syncTime'] = time();
        $this->getLiveStatisticsDao()->update($statistics['id'], $statistics);
    }

    public function getCheckinStatisticsByLiveId($liveId)
    {
        return $this->getLiveStatisticsDao()->getByLiveIdAndType($liveId, self::STATISTICS_TYPE_CHECKIN);
    }

    public function getVisitorStatisticsByLiveId($liveId)
    {
        return $this->getLiveStatisticsDao()->getByLiveIdAndType($liveId, self::STATISTICS_TYPE_VISITOR);
    }

    public function findCheckinStatisticsByLiveIds($liveIds)
    {
        $liveStatistics = $this->getLiveStatisticsDao()->findByLiveIdsAndType($liveIds, self::STATISTICS_TYPE_CHECKIN);

        return ArrayToolkit::index($liveStatistics, 'liveId');
    }

    public function findVisitorStatisticsByLiveIds($liveIds)
    {
        $liveStatistics = $this->getLiveStatisticsDao()->findByLiveIdsAndType($liveIds, self::STATISTICS_TYPE_VISITOR);

        return ArrayToolkit::index($liveStatistics, 'liveId');
    }

    protected function generateStatisticsByLiveIdAndType($liveId, $type)
    {
        if (!in_array($type, [self::STATISTICS_TYPE_CHECKIN, self::STATISTICS_TYPE_VISITOR])) {
            throw $this->createService(CommonException::ERROR_PARAMETER());
        }

        $liveActivity = $this->getLiveActivityService()->getBySyncIdGTAndLiveId($liveId);
        if (self::STATISTICS_TYPE_CHECKIN == $type) {
            if (!empty($liveActivity)) {
                $result = $this->getS2B2CFacadeService()->getS2B2CService()->getLiveRoomCheckinList($liveId);
            } else {
                $result = $this->getLiveClient()->getLiveRoomCheckinList($liveId);
            }
        } else {
            if (!empty($liveActivity)) {
                $result = $this->getS2B2CFacadeService()->getS2B2CService()->getLiveRoomHistory($liveId);
            } else {
                $result = $this->getLiveClient()->getLiveRoomHistory($liveId);
            }
        }
        $result['liveId'] = $liveId;

        $processor = LiveStatisticsProcessorFactory::create($type);
        $data = $processor->handlerResult($result);

        return [
            'liveId' => $liveId,
            'type' => $type,
            'data' => $data,
        ];
    }

    /**
     * @return EdusohoLiveClient
     */
    protected function getLiveClient()
    {
        return $this->biz['educloud.live_client'];
    }

    /**
     * @return LiveStatisticsDao
     */
    protected function getLiveStatisticsDao()
    {
        return $this->createDao('Live:LiveStatisticsDao');
    }

    /**
     * @return LiveActivityService
     */
    protected function getLiveActivityService()
    {
        return $this->createService('Activity:LiveActivityService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }

    /**
     * @return LiveMemberStatisticsDao
     */
    protected function getLiveMemberStatisticsDao()
    {
        return $this->createDao('LiveStatistics:LiveMemberStatisticsDao');
    }

    protected function getUserDao()
    {
        return $this->createDao('User:UserDao');
    }
}
