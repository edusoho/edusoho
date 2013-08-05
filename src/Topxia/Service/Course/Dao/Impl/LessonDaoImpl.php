<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\LessonDao;

class LessonDaoImpl extends BaseDao implements LessonDao
{
    protected $table = 'course_lesson';

    public function getLesson($id)
    {
        return $this->fetch($id);
    }

    public function findLessonsByIds(array $ids)
    {
        if(empty($ids)){
            return array();
        }
        $marks = str_repeat('?,', count($ids) - 1) . '?';
        $sql ="SELECT * FROM {$this->table} WHERE id IN ({$marks});";
        return $this->getConnection()->fetchAll($sql, $ids);
    }

    public function findLessonsByCourseId($courseId)
    {
        return $this->createQueryBuilder()
            ->select('*')->from($this->table, 'lesson')
            ->where("courseId = :courseId")
            ->orderBy('seq', 'ASC')
            ->setParameter(":courseId", $courseId)
            ->execute()
            ->fetchAll();
    }

    public function findLessonIdsByCourseId($courseId)
    {
        $sql = "SELECT id FROM {$this->table} WHERE  courseId = ? ORDER BY number ASC";
        return $this->getConnection()->executeQuery($sql, array($courseId))->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function getLessonCountByCourseId($courseId)
    {
        return $this->createQueryBuilder()
            ->select('COUNT(*)')->from($this->table, 'lesson')
            ->where("courseId = :courseId")
            ->setParameter(":courseId", $courseId)
            ->execute()
            ->fetchColumn(0);
    }

    public function getLessonMaxSeqByCourseId($courseId)
    {
        return $this->createQueryBuilder()
            ->select('MAX(seq)')->from($this->table, 'lesson')
            ->where("courseId = :courseId")
            ->setParameter(":courseId", $courseId)
            ->execute()
            ->fetchColumn(0);
    }

    public function findLessonsByChapterId($chapterId)
    {
        return $this->createQueryBuilder()
            ->select('*')->from($this->table, 'lesson')
            ->where("chapterId = :chapterId")
            ->orderBy('seq', 'ASC')
            ->setParameter(":chapterId", $chapterId)
            ->execute()
            ->fetchAll();
    }

    public function addLesson($lesson)
    {
        $id = $this->insert($lesson);
        return $this->getLesson($id);
    }

    public function updateLesson($id, $fields)
    {
        return $this->update($id, $fields);
    }

    public function deleteLesson($id)
    {
        return $this->delete($id);
    }

    public function deleteLessonsByCourseId($courseId)
    {
        return $this->createQueryBuilder()
            ->delete($this->table, 'lesson')
            ->where("courseId = :courseId")
            ->setParameter(":courseId", $courseId)
            ->execute();
    }
}