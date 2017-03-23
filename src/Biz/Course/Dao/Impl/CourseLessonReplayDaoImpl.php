<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseLessonReplayDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseLessonReplayDaoImpl extends GeneralDaoImpl implements CourseLessonReplayDao
{
    protected $table = 'course_lesson_replay';

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
            'orderbys' => array('replayId', 'createdTime'),
            'conditions' => array(
                'courseId = :courseId',
                'lessonId = :lessonId',
                'hidden = :hidden',
                'copyId = :copyId',
                'type = :type',
            ),
        );
    }

    public function deleteByLessonId($lessonId, $lessonType = 'live')
    {
        return $this->db()->delete($this->table, array('lessonId' => $lessonId, 'type' => $lessonType));
    }

    public function findByLessonId($lessonId, $lessonType = 'live')
    {
        $sql = "SELECT * FROM {$this->table()} WHERE lessonId = ? AND type = ? ORDER BY replayId ASC";

        return $this->db()->fetchAll($sql, array($lessonId, $lessonType));
    }

    public function deleteByCourseId($courseId, $lessonType = 'live')
    {
        return $this->db()->delete($this->table, array('courseId' => $courseId, 'type' => $lessonType));
    }

    public function getByCourseIdAndLessonId($courseId, $lessonId, $lessonType = 'live')
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId=? AND lessonId = ? AND type = ? ";

        return $this->db()->fetchAssoc($sql, array($courseId, $lessonId, $lessonType));
    }

    public function findByCourseIdAndLessonId($courseId, $lessonId, $lessonType = 'live')
    {
        $sql = "SELECT * FROM {$this->table()} WHERE courseId=? AND lessonId = ? AND type = ? ";

        return $this->db()->fetchAll($sql, array($courseId, $lessonId, $lessonType));
    }

    public function updateByLessonId($lessonId, $lessonType = 'live', $fields)
    {
        return $this->db()->update($this->table, $fields, array('lessonId' => $lessonId, 'type' => $lessonType));
    }
}
