<?php

namespace Classroom\Service\Classroom\Dao;

interface ClassroomReviewDao
{
    public function getByUserIdAndClassroomId($userId, $classroomId);

    public function sumReviewRatingByClassroomId($classroomId);

    public function countReviewByClassroomId($classroomId);
}
