<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseSubcourseDao;
use PDO;

class CourseSubcourseDaoImpl extends BaseDao implements CourseSubcourseDao
{
    protected $table = 'course_subcourse';

    public function get($id)
    {
       $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
       return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findSubcoursesByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? ORDER BY sequence";
        return $this->getConnection()->fetchAll($sql, array($courseId)) ? : array();
    }

    public function findSubcoursesCountByCourseId($courseId)
    {
        $sql = "SELECT COUNT(id) FROM {$this->table} WHERE courseId = ?";
        return $this->getConnection()->fetchColumn($sql, array($courseId));
    }

    public function addSubcourse($fields)
    {
        $affected = $this->getConnection()->insert($this->table, $fields);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course package item error.');
        }
        return $this->get($this->getConnection()->lastInsertId());
    }

    public function delete($id)
    {
        $sql = "DELETE FROM {$this->table} WHERE id = ?";
        return $this->getConnection()->executeUpdate($sql, array($id));
    }

    public function update($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->get($id);
    }

}