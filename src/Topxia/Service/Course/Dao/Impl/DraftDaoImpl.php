<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\DraftDao;

class DraftDaoImpl extends BaseDao implements DraftDao
{
        protected $draftTable = 'course_draft';
        private $serializeFields = array(
        'data' => 'json',
    );

        public function getDraft($id)
    {
        $sql = "SELECT * FROM {$this->draftTable} WHERE id = ? LIMIT 1";

        $lesson = $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
        return $lesson ? $this->createSerializer()->unserialize($lesson, $this->serializeFields) : null;
    }

    public function getDrafts($courseId,$userId)
    {
        $sql = "SELECT * FROM {$this->draftTable} WHERE courseId = ? AND userId = ?";

        return $this->getConnection()->fetchAssoc($sql, array($courseId,$userId)) ? : null;
    }

    public function findDraftsByCourseId($courseId,$userId)
    {
        $sql = "SELECT * FROM {$this->draftTable} WHERE courseId = ? AND userId = ? ";
        return $this->getConnection()->fetchAll($sql, array($courseId,$userId));
    }

    public function addDraft($lesson)
    {

        $lesson = $this->createSerializer()->serialize($lesson, $this->serializeFields);
        $affected = $this->getConnection()->insert($this->draftTable, $lesson);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert lesson error.');
        }
        return $this->getDraft($this->getConnection()->lastInsertId());
    }

     public function updateTextDraft($userId,$courseId, $fields)
     {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->draftTable, $fields, array('courseId' => $courseId,'userId' => $userId));
        return $this->getDrafts($courseId,$userId);
    }

    public function deleteDraft($courseId,$userId)
    {
        $sql = "DELETE FROM {$this->draftTable} WHERE courseId = ? AND userId = ?";
        return $this->getConnection()->executeUpdate($sql, array($courseId,$userId));
    }

}