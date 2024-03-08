<?php

namespace Biz\User\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface StatusDao extends AdvancedDaoInterface
{
    public function deleteByUserIdAndTypeAndObject($userId, $type, $objectType, $objectId);

    public function deleteByCourseId($courseId);

    public function findByCourseId($courseId);
}
