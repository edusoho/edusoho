<?php

namespace Biz\LiveStatistics\Service;

interface LiveCloudStatisticsService
{
    public function searchCourseMemberLiveData($conditions, $start, $limit);

    public function sumWatchDurationByCourseIdGroupByUserId($courseId);

    public function countLiveMembers($conditions);

    public function getAvgWatchDurationByLiveId($liveId, $userIds);

    public function getLiveData($activityId);

    public function syncLiveMemberData($activityId);

    public function processEsLiveMemberData($liveActivity, $memberData);

    public function processThirdPartyLiveMemberData($liveActivity, $memberData);
}
