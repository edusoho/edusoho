<?php

namespace Biz\UserLearnStatistics\Service;

interface LearnStatisticsService
{
    public function getUserOverview($userId);

    public function getLearningCourseDetails($userId, $start, $limit);

    public function getDailyLearnData($userId, $startTime, $endTime);
}