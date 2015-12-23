<?php
namespace Classroom\Service\Classroom\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Classroom\Service\Classroom\Dao\ClassroomCourseDao;

class ClassroomCourseDaoImpl extends BaseDao implements ClassroomCourseDao
{
    protected $table = 'classroom_courses';

    private $serializeFields = array(
        'tagIds' => 'json'
    );

    public function getTable()
    {
        return $this->table;
    }

    public function addCourse($course)
    {
        $course = $this->createSerializer()->serialize($course, $this->serializeFields);

        $affected = $this->getConnection()->insert($this->table, $course);
        $this->clearCached();

        if ($affected <= 0) {
            throw $this->createDaoException('Insert classroom_courses error.');
        }

        return $this->getCourse($this->getConnection()->lastInsertId());
    }

    public function update($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        $this->clearCached();
        return $this->getCourse($id);
    }

    public function updateByParam($params, $fields)
    {
        $this->getConnection()->update($this->table, $fields, $params);
        $this->clearCached();
    }

    public function deleteCourseByClassroomIdAndCourseId($classroomId, $courseId)
    {
        $result = $this->getConnection()->delete($this->table, array('classroomId' => $classroomId, 'courseId' => $courseId));
        $this->clearCached();
        return $result;
    }

    public function getCourse($id)
    {
        $that = $this;

        return $this->fetchCached("id:{$id}", $id, function ($id) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} where id=? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($id)) ?: null;
        }

        );
    }

    public function findClassroomIdsByCourseId($courseId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}", $courseId, function ($courseId) use ($that) {
            $sql = "SELECT classroomId FROM {$that->getTable()} where courseId=?";
            return $that->getConnection()->fetchAll($sql, array($courseId));
        }

        );
    }

    public function findClassroomByCourseId($courseId)
    {
        $that = $this;

        return $this->fetchCached("courseId:{$courseId}:limit:1", $courseId, function ($courseId) use ($that) {
            $sql = "SELECT classroomId FROM {$that->getTable()} where courseId = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($courseId)) ?: null;
        }

        );
    }

    public function findClassroomCourse($classroomId, $courseId)
    {
        $that = $this;

        return $this->fetchCached("classroomId:{$classroomId}:courseId:{$courseId}", $classroomId, $courseId, function ($classroomId, $courseId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} where classroomId = ? AND courseId = ? LIMIT 1";
            return $that->getConnection()->fetchAssoc($sql, array($classroomId, $courseId)) ?: null;
        }

        );
    }

    public function getCourseByClassroomIdAndCourseId($classroomId, $courseId)
    {
        return $this->findClassroomCourse($classroomId, $courseId);
    }

    public function deleteCoursesByClassroomId($classroomId)
    {
        $sql    = "DELETE FROM {$this->table} WHERE classroomId = ?";
        $result = $this->getConnection()->executeUpdate($sql, array($classroomId));
        $this->clearCached();
        return $result;
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

        $marks     = str_repeat('?,', count($courseIds) - 1).'?';
        $sql       = "SELECT * FROM {$this->table} WHERE classroomId = ? AND courseId IN ({$marks}) ORDER BY seq ASC;";
        $courseIds = array_merge(array($classroomId), $courseIds);

        return $this->getConnection()->fetchAll($sql, $courseIds) ?: array();
    }

    public function findCoursesByClassroomId($classroomId)
    {
        $that = $this;

        return $this->fetchCached("classroomId:{$classroomId}", $classroomId, function ($classroomId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE classroomId = ? ORDER BY seq ASC;";
            return $that->getConnection()->fetchAll($sql, array($classroomId)) ?: array();
        }

        );
    }

    public function findActiveCoursesByClassroomId($classroomId)
    {
        $that = $this;

        return $this->fetchCached("classroomId:{$classroomId}:disabled:0", $classroomId, function ($classroomId) use ($that) {
            $sql = "SELECT * FROM {$that->getTable()} WHERE classroomId = ? AND disabled = 0 ORDER BY seq ASC;";
            return $that->getConnection()->fetchAll($sql, array($classroomId)) ?: array();
        }

        );
    }

    public function findCoursesByCoursesIds($courseIds)
    {
        if (empty($courseIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE courseId IN ({$marks}) ORDER BY seq ASC;";

        return $this->getConnection()->fetchAll($sql, $courseIds) ?: array();
    }

    public function findClassroomsByCoursesIds($courseIds)
    {
        if (empty($courseIds)) {
            return array();
        }

        $marks = str_repeat('?,', count($courseIds) - 1).'?';
        $sql   = "SELECT * FROM {$this->table} WHERE courseId IN ({$marks}) AND disabled=0 ORDER BY seq ASC;";

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
