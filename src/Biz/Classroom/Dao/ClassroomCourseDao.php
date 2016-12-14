<?php


namespace Biz\Classroom\Dao;


use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ClassroomCourseDao extends  GeneralDaoInterface
{
    public function findActiveCoursesByClassroomId($classroomId);
}