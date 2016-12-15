<?php

namespace Biz\User\Dao;

interface StatusDao
{
    public function deleteByUserIdAndTypeAndObject($userId, $type, $objectType, $objectId);

    public function deleteByCourseIdAndTypeAndObject($courseId, $type, $objectType, $objectId);

    public function findByCourseId($courseId);
}
