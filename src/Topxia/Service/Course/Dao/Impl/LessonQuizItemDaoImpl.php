<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\LessonQuizItemDao;

class LessonQuizItemDaoImpl extends BaseDao implements LessonQuizItemDao
{
    protected $table = 'lesson_quiz_item';

    public function addLessonQuizItem($lessonQuizItemInfo)
    {
        $affected = $this->getConnection()->insert($this->table, $lessonQuizItemInfo);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert course lessonQuizItem error.');
        }
        return $this->getLessonQuizItem($this->getConnection()->lastInsertId());
    }

    public function findLessonQuizItemsByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function getLessonQuizItem($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id));
    }

    public function findLessonQuizItemsByCourseIdAndLessonId($courseId, $lessonId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE courseId = ? AND lessonId = ?";
        return $this->getConnection()->fetchAll($sql, array($courseId, $lessonId));
    }

    public function findItemIdsByCourseIdAndLessonId($courseId, $lessonId)
    {
        $sql = "SELECT id FROM {$this->table} WHERE courseId = ? AND lessonId = ?";
        return $this->getConnection()->fetchAll($sql, array($courseId, $lessonId));
    }

    public function updateLessonQuizItem($id, $fields)
    {
        $this->getConnection()->update($this->table, $fields, array('id' => $id));
        return $this->getLessonQuizItem($id);
    }

    public function deleteLessonQuizItem($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

}