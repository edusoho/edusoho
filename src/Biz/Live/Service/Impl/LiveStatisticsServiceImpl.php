<?php

namespace Biz\Live\Service\Impl;

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
        $this->insertLiveStatistics($liveId, $data, self::STATISTICS_TYPE_CHECKIN);
    }

    public function createLiveHistoryStatistics($liveId)
    {
        $result = $this->getLiveClient()->getLiveRoomCheckinList($liveId);
        $processor = LiveStatsisticsProcessorFactory::create(self::STATISTICS_TYPE_HISTORY);
        $data = $processor->handlerResult($result);
        $this->insertLiveStatistics($liveId, $data, self::STATISTICS_TYPE_HISTORY);
    }

    private function insertLiveStatistics($liveId, $data, $type)
    {
        return $this->getLiveStatisticsDao()->create(array(
            'liveId' => $liveId,
            'data' => $data,
            'type' => $type,
            'createdTime' => time(),
            'updatedTime' => time(),
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
