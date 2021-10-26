<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseLessonReplayDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseLessonReplayDaoImpl extends GeneralDaoImpl implements CourseLessonReplayDao
{
    protected $table = 'course_lesson_replay';

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
            'orderbys' => ['replayId', 'createdTime'],
            'conditions' => [
                'courseId = :courseId',
                'lessonId = :lessonId',
                'hidden = :hidden',
                'copyId = :copyId',
                'type = :type',
            ],
        ];
    }

    public function deleteByLessonId($lessonId, $lessonType = 'live')
    {
        return $this->db()->delete($this->table, ['lessonId' => $lessonId, 'type' => $lessonType]);
    }

    public function findByLessonId($lessonId, $lessonType = 'live')
    {
        $sql = "SELECT * FROM {$this->table()} WHERE lessonId = ? AND type = ? ORDER BY replayId ASC";

        return $this->db()->fetchAll($sql, [$lessonId, $lessonType]);
    }

    public function findByLessonIds($lessonIds, $lessonType = 'live')
    {
        if (empty($lessonIds)) {
            return [];
        }

        $marks = str_repeat('?,', count($lessonIds) - 1).'?';

        $sql = "SELECT * FROM {$this->table()} WHERE lessonId IN ({$marks}) AND type = ? ORDER BY replayId ASC";

        return $this->db()->fetchAll($sql, array_merge($lessonIds, [$lessonType]));
    }

    public function deleteByCourseId($courseId, $lessonType = 'live')
    {
        return $this->db()->delete($this->table, ['courseId' => $courseId, 'type' => $lessonType]);
    }

    public function getByCourseIdAndLessonId($courseId, $lessonId, $lessonType = 'live')
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId=? AND lessonId = ? AND type = ? ";

        return $this->db()->fetchAssoc($sql, [$courseId, $lessonId, $lessonType]);
    }

    public function getByReplayId($replayId)
    {
        return $this->getByFields(['replayId' => $replayId]);
    }

    public function findByCourseIdAndLessonId($courseId, $lessonId, $lessonType = 'live')
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId=? AND lessonId = ? AND type = ? ";

        return $this->db()->fetchAll($sql, [$courseId, $lessonId, $lessonType]);
    }

    public function updateByLessonId($lessonId, $lessonType = 'live', $fields)
    {
        return $this->db()->update($this->table, $fields, ['lessonId' => $lessonId, 'type' => $lessonType]);
    }
}
