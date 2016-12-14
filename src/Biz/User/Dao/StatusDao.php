<?php

namespace Biz\User\Dao;

interface StatusDao
{
    public function searchByUserIds($userIds, $start, $limit);

    public function countByUserIds($userIds);

    public function deleteByUserIdAndTypeAndObject($userId, $type, $objectType, $objectId);

    public function deleteByCourseIdAndTypeAndObject($courseId, $type, $objectType, $objectId);
}
