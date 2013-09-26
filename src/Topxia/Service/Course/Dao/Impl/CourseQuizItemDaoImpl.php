<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseQuizItemDao;

class CourseQuizItemDaoImpl extends BaseDao implements CourseQuizItemDao
{
    protected $table = 'course_quiz_item';

    public function addQuizItem($quizItemInfo)
    {
        $affected = $this->getConnection()->insert($this->table, $quizItemInfo);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course lessonQuizItem error.');
        }
        return $this->getQuizItem($this->getConnection()->lastInsertId());
    }

    public function getQuizItemsCount($courseId, $lessonId)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE  courseId = ? AND lessonId = ?";
        return $this->getConnection()->fetchColumn($sql, array($courseId, $lessonId)); 
    }

    public function findQuizItemsByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function getQuizItem($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function findQuizItemsByCourseIdAndLessonId($courseId, $lessonId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND lessonId = ?";
        return $this->getConnection()->fetchAll($sql, array($courseId, $lessonId));
    }

    public function findItemIdsByCourseIdAndLessonId($courseId, $lessonId)
    {
        $sql = "SELECT id FROM {$this->table} WHERE courseId = ? AND lessonId = ?";
        return $this->getConnection()->fetchAll($sql, array($courseId, $lessonId));
    }

    public function updateQuizItem($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getQuizItem($id);
    }

    public function deleteQuizItem($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

}