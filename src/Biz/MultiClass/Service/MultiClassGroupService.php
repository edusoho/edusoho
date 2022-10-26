<?php

namespace Biz\MultiClass\Service;

interface MultiClassGroupService
{
    const MULTI_CLASS_GROUP_NAME = '分组';

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

    public function sortMultiClassGroup($multiClassId);

    public function updateMultiClassGroup($id, $fields);

    public function getLatestGroup($multiClassId);

    public function batchUpdateGroupAssistant($multiClassId, $groupIds, $assistantId);

    public function batchDeleteMultiClassGroups($ids);
}
