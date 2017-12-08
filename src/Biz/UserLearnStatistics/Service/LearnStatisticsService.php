<?php

namespace Biz\UserLearnStatistics\Service;

interface LearnStatisticsService
{
    public function getUserOverview($userId);

    public function findLearningCourseDetails($userId, $start, $limit);

    public function getDailyLearnData($userId, $startTime, $endTime);

    public function statisticsDataSearch($conditions);

    public function statisticsDataCount($conditions);

    public function searchTotalStatistics($conditions, $order, $start, $limit);

    public function countTotalStatistics($conditions);

    public function searchDailyStatistics($conditions, $order, $start, $limit);

    public function countDailyStatistics($conditions);

    public function batchCreateTotalStatistics($conditions);

    public function batchCreatePastDailyStatistics($conditions);

    public function batchCreateDailyStatistics($conditions);

    public function batchDeletePastDailyStatistics($conditions);

    public function searchLearnData($conditions, $fields);

    public function storageDailyStatistics($limit = 1000);

    public function getStatisticsSetting();

    public function setStatisticsSetting();
}
