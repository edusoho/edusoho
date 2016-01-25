<?php
namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\CourseNoteLikeDao;

class CourseNoteLikeDaoImpl extends BaseDao implements CourseNoteLikeDao
{
    protected $table = 'course_note_like';

    public function getNoteLike($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE id = ? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($id)) ?: null;
    }

    public function getNoteLikeByNoteIdAndUserId($noteId, $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE noteId = ? AND userId=? LIMIT 1";

        return $this->getConnection()->fetchAssoc($sql, array($noteId, $userId)) ?: null;
    }

    public function addNoteLike($noteLike)
    {
        $affected = $this->getConnection()->insert($this->table, $noteLike);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert noteLike error.');
        }

        return $this->getNoteLike($this->getConnection()->lastInsertId());
    }

    public function deleteNoteLikeByNoteIdAndUserId($noteId, $userId)
    {
        return $this->getConnection()->delete($this->table, array('noteId' => $noteId, 'userId' => $userId));
    }

    public function findNoteLikesByUserId($userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? ORDER BY createdTime DESC";

        return $this->getConnection()->fetchAll($sql, array($userId));
    }

    public function findNoteLikesByNoteId($noteId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE noteId = ? ORDER BY createdTime DESC";

        return $this->getConnection()->fetchAll($sql, array($noteId));
    }

    public function findNoteLikesByNoteIds(array $noteIds)
    {
        if (empty($noteIds)) {
            return array();
        }
        $marks = str_repeat('?,', count($noteIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE noteId IN ({$marks});";

        return $this->getConnection()->fetchAll($sql, $noteIds);
    }

    public function findNoteLikesByNoteIdsAndUserId(array $noteIds, $userId)
    {
        if (empty($noteIds) || empty($userId)) {
            return array();
        }
        $marks = str_repeat('?,', count($noteIds) - 1).'?';
        $sql = "SELECT * FROM {$this->table} WHERE userId = ? AND noteId IN ({$marks});";

        $noteIds = array_merge(array($userId), $noteIds);
        return $this->getConnection()->fetchAll($sql, $noteIds);
    }

    public function deleteNoteLikesByNoteId($noteId)
    {
        return $this->getConnection()->delete($this->table, array('noteId' => $noteId));
    }
}
