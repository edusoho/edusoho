<?php

namespace Biz\LearnStatistics\Service;

interface LearnStatisticsService
{
    public function getLearnStatistics($id, $lock = false);

    public function createLearnStatistics($fields);    

    public function updateLearnStatistics($id, $fields);

    public function findLearnStatisticsByIds($ids);

    public function searchLearnStatisticss($conditions, $orders, $start, $limit);

    public function countLearnStatistics($conditions);
}