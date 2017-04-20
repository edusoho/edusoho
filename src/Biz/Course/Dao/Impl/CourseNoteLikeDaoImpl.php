<?php

namespace Biz\Course\Dao\Impl;

use Biz\Course\Dao\CourseNoteLikeDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class CourseNoteLikeDaoImpl extends GeneralDaoImpl implements CourseNoteLikeDao
{
    protected $table = 'course_note_like';

    public function getByNoteIdAndUserId($noteId, $userId)
    {
        return $this->getByFields(array(
            'noteId' => $noteId,
            'userId' => $userId,
        ));
    }

    public function deleteByNoteIdAndUserId($noteId, $userId)
    {
        return $this->db()->delete($this->table, array('noteId' => $noteId, 'userId' => $userId));
    }

    public function findByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? ORDER BY createdTime DESC";

        return $this->db()->fetchAll($sql, array($userId));
    }

    public function findByNoteId($noteId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE noteId = ? ORDER BY createdTime DESC";

        return $this->db()->fetchAll($sql, array($noteId));
    }

    public function findByNoteIds(array $noteIds)
    {
        return $this->findInField('noteId', $noteIds);
    }

    public function findByNoteIdsAndUserId(array $noteIds, $userId)
    {
        if (empty($noteIds) || empty($userId)) {
            return array();
        }
        $marks = str_repeat('?,', count($noteIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND noteId IN ({$marks});";

        $noteIds = array_merge(array($userId), $noteIds);

        return $this->db()->fetchAll($sql, $noteIds);
    }

    public function deleteByNoteId($noteId)
    {
        return $this->db()->delete($this->table, array('noteId' => $noteId));
    }

    public function declares()
    {
        return array(
            'timestamps' => array('createdTime'),
        );
    }
}
