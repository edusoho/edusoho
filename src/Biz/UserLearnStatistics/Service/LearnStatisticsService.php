<?php

namespace Biz\UserLearnStatistics\Service;

interface LearnStatisticsService
{
    public function searchTotalStatistics($conditions, $order, $start, $limit);

    public function countTotalStatistics($conditions);

    public function searchDailyStatistics($conditions, $order, $start, $limit);

    public function batchCreateTotalStatistics($conditions);

    public function batchCreatePastDailyStatistics($conditions);

    public function batchCreateDailyStatistics($conditions);

    public function batchDeletePastDailyStatistics($conditions);

    public function searchLearnData($conditions, $fields);

    public function storageDailyStatistics($limit = 1000);

    public function getStatisticsSetting();
}
