<?php

namespace Biz\TeacherQualification\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface TeacherQualificationDao extends GeneralDaoInterface
{
    public function getByUserId($userId);

    public function findByUserIds($userIds);

    public function countTeacherQualification($conditions);

    public function searchTeacherQualification($conditions, $orderBys, $start, $limit);
}
