<?php

namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface StatusDao extends GeneralDaoInterface
{
    public function deleteByUserIdAndTypeAndObject($userId, $type, $objectType, $objectId);

    public function deleteByCourseIdAndTypeAndObject($courseId, $type, $objectType, $objectId);

    public function findByCourseId($courseId);
}
