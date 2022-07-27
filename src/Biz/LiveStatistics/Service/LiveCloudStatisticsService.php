<?php

namespace Biz\LiveStatistics\Service;

interface LiveCloudStatisticsService
{
    public function searchCourseMemberLiveData($conditions, $start, $limit);

    public function sumWatchDurationByCourseIdGroupByUserId($courseId);

    public function countLiveMembersByLiveId($liveId);

    public function countLiveMembers($conditions);

    public function getAvgWatchDurationByLiveId($liveId, $userIds);

    public function getLiveData($task);

    public function processEsLiveMemberData($activity, $memberData);

    public function processGeneralLiveMemberData($activity, $memberData);
}
