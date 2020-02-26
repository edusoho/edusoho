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
    const STATISTICS_TYPE_HISTORY = 'history';

    public function createLiveCheckinStatistics($liveId)
    {
        $result = $this->getLiveClient()->getLiveRoomCheckinList($liveId);
        $processor = LiveStatsisticsProcessorFactory::create(self::STATISTICS_TYPE_CHECKIN);
        $data = $processor->handlerResult($result);
        $this->insertLiveStatistics($data, self::STATISTICS_TYPE_CHECKIN, 'checkin');
    }

    public function findCheckinStatisticsByLiveIds($liveIds)
    {
        $liveStatistics = $this->getLiveStatisticsDao()->findByLiveIdsAndType($liveIds, 'checkin');

        return ArrayToolkit::index($liveStatistics, 'liveId');
    }

    public function findHistoryStatisticsByLiveIds($liveIds)
    {
        $liveStatistics = $this->getLiveStatisticsDao()->findByLiveIdsAndType($liveIds, 'history');

        return ArrayToolkit::index($liveStatistics, 'liveId');
    }

    public function findByLiveIdsAndType(array $liveIds, $type)
    {
        $liveStatistics = $this->getLiveStatisticsDao()->findByLiveIdsAndType($liveIds, $type);

        return ArrayToolkit::index($liveStatistics, 'liveId');
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
