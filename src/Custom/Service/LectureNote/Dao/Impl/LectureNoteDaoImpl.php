<?php
namespace Custom\Service\LectureNote\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Custom\Service\LectureNote\Dao\LectureNoteDao;

class LectureNoteDaoImpl extends BaseDao implements LectureNoteDao
{
    protected $table = 'lecture_note';

    public function getLectureNote($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";
        return $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function addLectureNote(array $lectureNote)
    {
        $affected = $this->getConnection()->insert($this->table, $lectureNote);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert lectureNote error.');
        }
        return $this->getLectureNote($this->getConnection()->lastInsertId());
    }

    public function findAllLectureNotes()
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY createdTime DESC";
        return $this->getConnection()->fetchAll($sql);
    }

    public function deleteLectureNote($id)
    {
        return $this->getConnection()->delete($this->table, array('id' => $id));
    }

    public function findLectureNotesByLessonId($lessonId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE lessonId = ? ORDER BY createdTime DESC";
        return $this->getConnection()->fetchAll($sql, array($lessonId)) ? : array();
    }
}