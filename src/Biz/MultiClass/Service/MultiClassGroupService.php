<?php

namespace Biz\MultiClass\Service;

interface MultiClassGroupService
{
    public function findGroupsByMultiClassId($multiClassId);

    public function findGroupsByCourseId($courseId);

    public function getLiveGroupByUserIdAndCourseId($userId, $courseId, $liveId);

    public function createLiveGroup($fields);

    public function batchCreateLiveGroups($liveGroups);
}
