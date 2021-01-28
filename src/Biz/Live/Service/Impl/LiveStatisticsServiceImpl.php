<?php

namespace Biz\Live\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\LiveActivityService;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Live\Dao\LiveStatisticsDao;
use Biz\Live\LiveStatisticsProcessor\LiveStatisticsProcessorFactory;
use Biz\Live\Service\LiveStatisticsService;
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
            return $this->createLiveVisitorStatistics($liveId);
        }

        $statistics = $this->generateStatisticsByLiveIdAndType($liveId, self::STATISTICS_TYPE_VISITOR);

        return empty($statistics['data']['detail']) ? $exist : $this->getLiveStatisticsDao()->update($exist['id'], $statistics);
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
}
