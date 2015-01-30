<?php
namespace Topxia\Service\Classroom\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Classroom\Dao\ClassroomCourseDao;

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

    public function getCourse($id)
    {
        $sql = "SELECT * FROM {$this->table} where id=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getCourseByClassroomIdAndCourseId($classroomId,$courseId)
    {
        $sql = "SELECT * FROM {$this->table} where classroomId=? and courseId=? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($classroomId,$courseId)) ? : null;
    }

    public function deleteCoursesByClassroomId($classroomId)
    {
        $sql ="DELETE FROM {$this->table} WHERE classroomId = ?";
        return $this->getConnection()->executeUpdate($sql, array($classroomId));
    }

    public function searchCourses($conditions,$orderBy,$start,$limit)
    {
        $this->filterStartLimit($start, $limit);

        $builder = $this->_createSearchBuilder($conditions)
            ->select('*')
            ->setFirstResult($start)
            ->setMaxResults($limit)
            ->addOrderBy($orderBy[0], $orderBy[1]);
  
        return $builder->execute()->fetchAll() ? : array();  
    }

    private function _createSearchBuilder($conditions)
    {

        $builder = $this->createDynamicQueryBuilder($conditions)
            ->from($this->table,$this->table)
            ->andWhere('classroomId =:classroomId')
            ->andWhere('courseId = :courseId');

        return $builder;
    }
}