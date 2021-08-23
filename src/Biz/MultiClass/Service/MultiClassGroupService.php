<?php

namespace Biz\MultiClass\Service;

interface MultiClassGroupService
{
    public function findGroupsByIds($ids);

    public function findGroupsByMultiClassId($multiClassId);

    public function getMultiClassGroup($id);

    public function createMultiClassGroups($courseId, $multiClass);

    public function findGroupsByCourseId($courseId);

    public function getLiveGroupByUserIdAndCourseId($userId, $courseId, $liveId);

    public function createLiveGroup($fields);

    public function batchCreateLiveGroups($liveGroups);

    public function setGroupNewStudent($multiClass, $studentId);

    public function deleteMultiClassGroup($id);

    public function updateMultiClassGroup($id, $fields);

    public function getLatestGroup($multiClassId);
}
