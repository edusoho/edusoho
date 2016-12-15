<?php


namespace Biz\Classroom\Dao\Impl;


use Biz\Classroom\Dao\ClassroomCourseDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class ClassroomCourseDaoImpl extends GeneralDaoImpl implements ClassroomCourseDao
{
    protected $table = 'classroom_courses';

    public function declares()
    {
        return array(
            'orderbys'   => array('seq'),
            'conditions' => array(
                'classroomId =:classroomId',
                'courseId = :courseId'
            ),
        );
    }

    public function findActiveCoursesByClassroomId($classroomId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE classroomId = ? AND disabled = 0 ORDER BY seq ASC;";
        return $this->db()->fetchAll($sql, array($classroomId)) ?: array();
    }

}