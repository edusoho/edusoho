<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseChapterDao;

class CourseChapterDaoImpl extends BaseDao implements CourseChapterDao
{
    protected $table = 'course_chapter';

    public function getChapter($id)
    {
        return $this->fetch($id);
    }

    public function addChapter(array $chapter)
    {
        $id = $this->insert($chapter);
    	return $this->getChapter($id);
    }

    public function findChaptersByCourseId($courseId)
    {
        return $this->createQueryBuilder()
            ->select('*')->from($this->table, 'chapter')
            ->where("courseId = :courseId")
            ->orderBy('seq', 'ASC')
            ->setParameter(":courseId", $courseId)
            ->execute()
            ->fetchAll();
    }

    public function getChapterCountByCourseId($courseId)
    {
        return $this->createQueryBuilder()
            ->select('COUNT(*)')->from($this->table, 'chapter')
            ->where("courseId = :courseId")
            ->setParameter(":courseId", $courseId)
            ->execute()
            ->fetchColumn(0);
    }

    public function getChapterMaxSeqByCourseId($courseId)
    {
        return $this->createQueryBuilder()
            ->select('MAX(seq)')->from($this->table, 'chapter')
            ->where("courseId = :courseId")
            ->setParameter(":courseId", $courseId)
            ->execute()
            ->fetchColumn(0);
    }

    public function updateChapter($id, array $chapter)
    {
        return $this->update($id, $chapter);
    }

    public function deleteChapter($id)
    {
        return $this->delete($id);
    }

    public function deleteChaptersByCourseId($courseId)
    {
        $sql = "DELETE FROM {$this->table} WHERE courseId = ?";
        return $this->getConnection()->executeUpdate($sql, array($courseId));
    }

}