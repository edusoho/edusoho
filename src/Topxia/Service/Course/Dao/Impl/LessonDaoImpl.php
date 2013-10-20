<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\LessonDao;

class LessonDaoImpl extends BaseDao implements LessonDao
{
    protected $table = 'course_lesson';

    public function getLesson($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function findLessonsByIds(array $ids)
    {
        if(empty($ids)){ return array(); }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findLessonsByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? ORDER BY seq ASC";
        return $this->getConnection()->fetchAll($sql, array($courseId));
    }

    public function findLessonIdsByCourseId($courseId)
    {
        $sql = "SELECT id FROM {$this->table} WHERE  courseId = ? ORDER BY number ASC";
        return $this->getConnection()->executeQuery($sql, array($courseId))->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getLessonCountByCourseId($courseId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE courseId = ? ";
        return $this->getConnection()->fetchColumn($sql, array($courseId));
    }

    public function getLessonMaxSeqByCourseId($courseId)
    {
        $sql = "SELECT MAX(seq) FROM {$this->table} WHERE  courseId = ?";
        return $this->getConnection()->fetchColumn($sql, array($courseId));
    }

    public function findLessonsByChapterId($chapterId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE chapterId = ? ORDER BY seq ASC";
        return $this->getConnection()->fetchAll($sql, array($chapterId));
    }

    public function getLessonByMediaId($mediaId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE mediaId = ? order by createdTime DESC LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($mediaId)) ? : null;
    }

    public function addLesson($lesson)
    {
        $affected = $this->getConnection()->insert($this->table, $lesson);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course lesson error.');
        }
        return $this->getLesson($this->getConnection()->lastInsertId());
    }

    public function updateLesson($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getLesson($id);
    }

    public function deleteLessonsByCourseId($courseId)
    {
        $sql = "DELETE FROM {$this->table} WHERE courseId = ?";
        return $this->getConnection()->executeUpdate($sql, array($courseId));
    }

    public function deleteLesson($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

}