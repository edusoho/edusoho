<?php

namespace Biz\Classroom\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ClassroomCourseDao extends GeneralDaoInterface
{
    public function updateByParam($params, $fields);

    public function deleteByClassroomIdAndCourseId($classroomId, $courseId);

    public function getByClassroomIdAndCourseId($classroomId, $courseId);

    public function deleteByClassroomId($classroomId);

    public function findClassroomIdsByCourseId($courseId);

    public function findByClassroomIdAndCourseIds($classroomId, $courseIds);

    public function findByClassroomId($classroomId);

    public function findByCoursesIds($courseIds);

    public function findByCourseSetIds($courseSetIds);

    public function findEnabledByCoursesIds($courseIds);

    public function getClassroomIdByCourseId($courseId);

    public function getByCourseSetId($courseSetId);

    public function findActiveCoursesByClassroomId($classroomId);

    public function countCourseTasksByClassroomId($classroomId);
}
