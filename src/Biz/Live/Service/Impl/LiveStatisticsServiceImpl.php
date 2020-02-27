<?php

namespace Biz\Live\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Live\Dao\LiveStatisticsDao;
use Biz\Live\LiveStatisticsProcessor\LiveStatsisticsProcessorFactory;
use Biz\Live\Service\LiveStatisticsService;
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

        return $this->getLiveStatisticsDao()->update($exist['id'], $statistics);
    }

    public function updateVisitorStatistics($liveId)
    {
        $exist = $this->getVisitorStatisticsByLiveId($liveId);

        if (empty($exist)) {
            return $this->createLiveVisitorStatistics($liveId);
        }

        $statistics = $this->generateStatisticsByLiveIdAndType($liveId, self::STATISTICS_TYPE_VISITOR);

        return $this->getLiveStatisticsDao()->update($exist['id'], $statistics);
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
        if (!in_array($type, array(self::STATISTICS_TYPE_CHECKIN, self::STATISTICS_TYPE_VISITOR))) {
            throw $this->createService(CommonException::ERROR_PARAMETER());
        }

        if ($type == self::STATISTICS_TYPE_CHECKIN) {
            $result = $this->getLiveClient()->getLiveRoomCheckinList($liveId);
        } else {
            $result = $this->getLiveClient()->getLiveRoomHistory($liveId);
        }

        $result['liveId'] = $liveId;

        $processor = LiveStatsisticsProcessorFactory::create($type);
        $data = $processor->handlerResult($result);

        return array(
            'liveId' => $liveId,
            'type' => $type,
            'data' => $data,
        );
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
}
