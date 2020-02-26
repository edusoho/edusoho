<?php

namespace Biz\Live\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Live\Dao\LiveStatisticsDao;
use Biz\Live\LiveStatisticsProcessor\LiveStatsisticsProcessorFactory;
use Biz\Live\Service\LiveStatisticsService;
use Biz\Util\EdusohoLiveClient;

class LiveStatisticsServiceImpl extends BaseService implements LiveStatisticsService
{
    const STATISTICS_TYPE_CHECKIN = 'checkin';
    const STATISTICS_TYPE_VISITOR = 'visitor';

    public function createLiveCheckinStatistics($liveId)
    {
        $result = $this->getLiveClient()->getLiveRoomCheckinList($liveId);
        $processor = LiveStatsisticsProcessorFactory::create(self::STATISTICS_TYPE_CHECKIN);
        $data = $processor->handlerResult($result);
        $this->insertLiveStatistics($liveId, $data, self::STATISTICS_TYPE_CHECKIN);
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

    public function createLiveVisitorStatistics($liveId)
    {
        $result = $this->getLiveClient()->getLiveRoomHistory($liveId);
        $processor = LiveStatsisticsProcessorFactory::create(self::STATISTICS_TYPE_VISITOR);
        $data = $processor->handlerResult($result);
        $this->insertLiveStatistics($liveId, $data, self::STATISTICS_TYPE_VISITOR);
    }

    private function insertLiveStatistics($liveId, $data, $type)
    {
        return $this->getLiveStatisticsDao()->create(array(
            'liveId' => $liveId,
            'type' => $type,
            'data' => $data,
        ));
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
