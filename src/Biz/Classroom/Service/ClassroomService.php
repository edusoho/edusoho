<?php


namespace Biz\Classroom\Service;


interface ClassroomService
{
    public function searchMembers($conditions, $orderBy, $start, $limit);

    public function findClassroomsByIds(array $ids);

    public function findActiveCoursesByClassroomId($classroomId);

    public function findMembersByUserIdAndClassroomIds($userId, $classroomIds);

}