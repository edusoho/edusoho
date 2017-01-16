<?php

namespace Biz\Classroom\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ClassroomReviewDao extends GeneralDaoInterface
{
    public function getByUserIdAndClassroomId($userId, $classroomId);

    public function sumReviewRatingByClassroomId($classroomId);

    public function countReviewByClassroomId($classroomId);
}
