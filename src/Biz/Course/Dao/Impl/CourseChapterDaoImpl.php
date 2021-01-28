<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseChapterDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class CourseChapterDaoImpl extends AdvancedDaoImpl implements CourseChapterDao
{
    protected $table = 'course_chapter';

    public function getByCopyIdAndLockedCourseId($copyId, $courseId)
    {
        return $this->getByFields(['copyId' => $copyId, 'courseId' => $courseId]);
    }

    public function findByCopyId($copyId)
    {
        return $this->findByFields(['copyId' => $copyId]);
    }

    public function findChaptersByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? ORDER BY createdTime ASC";

        return $this->db()->fetchAll($sql, [$courseId]);
    }

    public function findLessonsByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId = ? AND type = 'lesson' ORDER BY createdTime ASC";

        return $this->db()->fetchAll($sql, [$courseId]);
    }

    public function getChapterCountByCourseIdAndType($courseId, $type)
    {
        $sql = "SELECT COUNT(*) FROM {$this->table()} WHERE  courseId = ? AND type = ?";

        return $this->db()->fetchColumn($sql, [$courseId, $type]);
    }

    public function getLastChapterByCourseIdAndType($courseId, $type)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE  courseId = ? AND type = ? ORDER BY seq DESC LIMIT 1";

        return $this->db()->fetchAssoc($sql, [$courseId, $type]) ?: null;
    }

    public function getLastChapterByCourseId($courseId)
    {
        $sql = "SELECT * FROM {$this->table()} WHERE  courseId = ? ORDER BY seq DESC LIMIT 1";

        return $this->db()->fetchAssoc($sql, [$courseId]) ?: null;
    }

    public function getChapterMaxSeqByCourseId($courseId)
    {
        $sql = "SELECT MAX(seq) FROM {$this->table()} WHERE  courseId = ?";

        return $this->db()->fetchColumn($sql, [$courseId]);
    }

    public function deleteChaptersByCourseId($courseId)
    {
        $sql = "DELETE FROM {$this->table()} WHERE courseId = ?";
        $result = $this->db()->executeUpdate($sql, [$courseId]);

        return $result;
    }

    public function findChaptersByCopyIdAndLockedCourseIds($copyId, $courseIds)
    {
        if (empty($courseIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($courseIds) - 1).'?';

        $parmaters = array_merge([$copyId], $courseIds);

        $sql = "SELECT * FROM {$this->table()} WHERE copyId= ? AND courseId IN ({$marks})";

        return $this->db()->fetchAll($sql, $parmaters) ?: [];
    }

    public function findByCopyIdsAndLockedCourseIds($copyIds, $courseIds)
    {
        if (empty($courseIds) || empty($copyIds)) {
            return [];
        }

        $copyIdMarks = str_repeat('?,', count($copyIds) - 1).'?';
        $courseIdMarks = str_repeat('?,', count($courseIds) - 1).'?';

        $parmaters = array_merge($copyIds, $courseIds);

        $sql = "SELECT * FROM {$this->table()} WHERE copyId IN ({$copyIdMarks}) AND courseId IN ({$courseIdMarks})";

        return $this->db()->fetchAll($sql, $parmaters) ?: [];
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
            'conditions' => [
                'id = :id',
                'copyId = :copyId',
                'courseId = :courseId',
                'seq >= :seq_GTE',
                'seq <= :seq_LTE',
                'seq < :seq_LT',
                'seq > :seq_GT',
                'type = :type',
                'type in (:types)',
                'status = :status',
                'syncId = :syncId',
                'syncId in (:syncIds)',
                'syncId > :syncIdGT',
            ],
        ];
    }
}
