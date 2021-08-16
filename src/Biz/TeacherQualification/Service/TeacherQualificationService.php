<?php

namespace Biz\TeacherQualification\Service;

interface TeacherQualificationService
{
    public function getByUserId($userId);

    public function changeQualification($userId, $fields);

    public function count($conditions);

    public function search($conditions, $orderBys, $start, $limit);
}
