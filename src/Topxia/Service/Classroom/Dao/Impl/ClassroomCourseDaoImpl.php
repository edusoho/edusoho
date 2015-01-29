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

}