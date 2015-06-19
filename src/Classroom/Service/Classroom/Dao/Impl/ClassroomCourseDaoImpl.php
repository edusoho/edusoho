<?php
namespace Classroom\Service\Classroom\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Classroom\Service\Classroom\Dao\ClassroomCourseDao;

class ClassroomCourseDaoImpl extends BaseDao implements ClassroomCourseDao
{

    protected $table = 'classroom_courses';

    private $serializeFields = array(
        'tagIds' => 'json',
    );

    public function addCourse($course)
    {
        $course = $this->createSerializer()->serialize($course, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $course);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert classroom_courses error.');
        }

        return $this->getCourse($this->getConnection()->lastInsertId());
    }

    public function update($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));

        return $this->getCourse($id);
    }

    public function updateByParam($params, $fields)
    {
        $this->getConnection()->update($this->table, $fields, $params);
    }

    public function deleteCourseByClassroomIdAndCourseId($classroomId, $courseId)
    {
        return $this->getConnection()->delete($this->table, array('classroomId' => $classroomId, 'courseId' => $courseId));
    }

    public function getCourse($id)
    {
        $sql = "SELECT * FROM {$this->table} where id=? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function findClassroomIdsByCourseId($courseId)
    {
        $sql = "SELECT classroomId FROM {$this->table} where courseId=?";

        return  $this->getConnection()->fetchAll($sql, array($courseId));
    }

    public function findClassroomByCourseId($courseId)
    {
        $sql = "SELECT classroomId FROM {$this->table} where courseId = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($courseId)) ?: null;
    }

    public function findClassroomCourse($classroomId, $courseId)
    {
        $sql = "SELECT * FROM {$this->table} where classroomId = ? AND courseId = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($classroomId, $courseId)) ?: null;
    }

    public function getCourseByClassroomIdAndCourseId($classroomId, $courseId)
    {
        $sql = "SELECT * FROM {$this->table} where classroomId=? AND courseId=? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($classroomId, $courseId)) ?: null;
    }

    public function deleteCoursesByClassroomId($classroomId)
    {
        $sql = "DELETE FROM {$this->table} WHERE classroomId = ?";

        return $this->getConnection()->executeUpdate($sql, array($classroomId));
    }

    public function searchCourses($conditions, $orderBy, $start, $limit)
    {
        $this->filterStartLimit($start, $limit);

        $builder = $this->_createSearchBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->addOrderBy('seq', 'ASC')
            ->addOrderBy($orderBy[0], $orderBy[1]);

        return $builder->execute()->fetchAll() ?: array();
    }

    public function findCoursesByClassroomIdAndCourseIds($classroomId, $courseIds)
    {
        if (empty($courseIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE classroomId = ? AND courseId IN ({$marks}) ORDER BY seq ASC;";
        $courseIds = array_merge(array($classroomId), $courseIds);

        return $this->getConnection()->fetchAll($sql, $courseIds) ?: array();
    }

    public function findCoursesByClassroomId($classroomId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE classroomId = ? ORDER BY seq ASC;";

        return $this->getConnection()->fetchAll($sql, array($classroomId)) ?: array();
    }

    public function findActiveCoursesByClassroomId($classroomId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE classroomId = ? AND disabled = 0 ORDER BY seq ASC;";

        return $this->getConnection()->fetchAll($sql, array($classroomId)) ?: array();
    }

    public function findCoursesByCoursesIds($courseIds)
    {
        if (empty($courseIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE courseId IN ({$marks}) ORDER BY seq ASC;";

        return $this->getConnection()->fetchAll($sql, $courseIds) ?: array();
    }

    public function findClassroomsByCoursesIds($courseIds)
    {
        if (empty($courseIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE courseId IN ({$marks}) AND disabled=0 ORDER BY seq ASC;";

        return $this->getConnection()->fetchAll($sql, $courseIds) ?: array();
    }

    private function _createSearchBuilder($conditions)
    {
        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table, $this->table)
            ->andWhere('classroomId =:classroomId')
            ->andWhere('courseId = :courseId');

        return $builder;
    }
}
