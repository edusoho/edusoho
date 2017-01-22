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
            )
        );
    }

    public function updateByParam($params, $fields)
    {
        return $this->db()->update($this->table, $fields, $params);
    }

    public function deleteByClassroomIdAndCourseId($classroomId, $courseId)
    {
        $result = $this->db()->delete($this->table, array('classroomId' => $classroomId, 'courseId' => $courseId));
        return $result;
    }

    public function findClassroomIdsByCourseId($courseId)
    {
        $sql = "SELECT classroomId FROM {$this->table()} where courseId=?";
        return $this->db()->fetchAll($sql, array($courseId));
    }

    public function getClassroomIdByCourseId($courseId)
    {
        $sql = "SELECT classroomId FROM {$this->table()} where courseId = ? LIMIT 1";
        return $this->db()->fetchAssoc($sql, array($courseId)) ?: null;
    }

    public function getByClassroomIdAndCourseId($classroomId, $courseId)
    {
        $sql = "SELECT * FROM {$this->table} where classroomId = ? AND courseId = ? LIMIT 1";
        return $this->db()->fetchAssoc($sql, array($classroomId, $courseId)) ?: null;
    }

    public function deleteByClassroomId($classroomId)
    {
        $sql    = "DELETE FROM {$this->table} WHERE classroomId = ?";
        $result = $this->db()->executeUpdate($sql, array($classroomId));
        return $result;
    }

    public function findByClassroomIdAndCourseIds($classroomId, $courseIds)
    {
        if (empty($courseIds)) {
            return array();
        }

        $marks     = str_repeat('?,', count($courseIds) - 1).'?';
        $sql       = "SELECT * FROM {$this->table} WHERE classroomId = ? AND courseId IN ({$marks}) ORDER BY seq ASC;";
        $courseIds = array_merge(array($classroomId), $courseIds);

        return $this->db()->fetchAll($sql, $courseIds) ?: array();
    }

    public function findByClassroomId($classroomId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE classroomId = ? ORDER BY seq ASC;";
        return $this->db()->fetchAll($sql, array($classroomId)) ?: array();
    }

    public function findByCoursesIds($courseIds)
    {
        if (empty($courseIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE courseId IN ({$marks}) ORDER BY seq ASC;";

        return $this->db()->fetchAll($sql, $courseIds) ?: array();
    }

    public function findEnabledByCoursesIds($courseIds)
    {
        if (empty($courseIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE courseId IN ({$marks}) AND disabled=0 ORDER BY seq ASC;";

        return $this->db()->fetchAll($sql, $courseIds) ?: array();
    }

    public function findActiveCoursesByClassroomId($classroomId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE classroomId = ? AND disabled = 0 ORDER BY seq ASC;";
        return $this->db()->fetchAll($sql, array($classroomId)) ?: array();
    }
}
