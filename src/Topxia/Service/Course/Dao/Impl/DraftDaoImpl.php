<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\DraftDao;

class DraftDaoImpl extends BaseDao implements DraftDao
{
        protected $draftTable = 'course_draft';
        protected $Table = 'edit_draft';
        private $serializeFields = array(
        'data' => 'json',
    );

        public function getDraft($id)
    {
        $sql = "SELECT * FROM {$this->draftTable} WHERE id = ? LIMIT 1";

        $draft = $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
        return $draft ? $this->createSerializer()->unserialize($draft, $this->serializeFields) : null;
    }

     public function getEditDraft($id)
    {
        $sql = "SELECT * FROM {$this->Table} WHERE id = ? LIMIT 1";

        $draft = $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
        return $draft ? $this->createSerializer()->unserialize($draft, $this->serializeFields) : null;
    }

    public function getDrafts($courseId,$userId)
    {
        $sql = "SELECT * FROM {$this->draftTable} WHERE courseId = ? AND userId = ?";

        return $this->getConnection()->fetchAssoc($sql, array($courseId,$userId)) ? : null;
    }

    public function getEditDrafts($courseId,$userId,$lessonId)
    {
        $sql = "SELECT * FROM {$this->Table} WHERE courseId = ? AND userId = ? And lessonId = ?";

        return $this->getConnection()->fetchAssoc($sql, array($courseId,$userId,$lessonId)) ? : null;
    }

    // public function findDraftsByCourseId($courseId,$userId)
    // {
    //     $sql = "SELECT * FROM {$this->draftTable} WHERE courseId = ? AND userId = ? ";
    //     return $this->getConnection()->fetchAll($sql, array($courseId,$userId));
    // }

        public function addDraft($draft)
        {

            $draft = $this->createSerializer()->serialize($draft, $this->serializeFields);
            $affected = $this->getConnection()->insert($this->draftTable, $draft);
            if ($affected <= 0) {
                throw $this->createDaoException('Insert draft error.');
            }
            return $this->getDraft($this->getConnection()->lastInsertId());
        }

        public function addEditDraft($draft)
        {

            $draft = $this->createSerializer()->serialize($draft, $this->serializeFields);
            $affected = $this->getConnection()->insert($this->Table, $draft);
            if ($affected <= 0) {
                throw $this->createDaoException('Insert draft error.');
            }
            return $this->getEditDraft($this->getConnection()->lastInsertId());
        }

     public function updateDraft($userId,$courseId, $fields)
     {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->draftTable, $fields, array('courseId' => $courseId,'userId' => $userId));
        return $this->getDrafts($courseId,$userId);
    }

    public function updateEditDraft($userId,$courseId,$lessonId,$fields)
     {
        $fields = $this->createSerializer()->serialize($fields, $this->serializeFields);
        $this->getConnection()->update($this->Table, $fields, array('courseId' => $courseId,'userId' => $userId,'lessonId' => $lessonId));
        return $this->getEditDrafts($courseId,$userId,$lessonId);
    }

    public function deleteDraftByCourseIdAndUserId($courseId,$userId)
    {
        $sql = "DELETE FROM {$this->draftTable} WHERE courseId = ? AND userId = ?";
        return $this->getConnection()->executeUpdate($sql, array($courseId,$userId));
    }

    public function deleteDraftByCourseIdAndUserIdAndLessonId($courseId,$userId,$lessonId)
    {
        $sql = "DELETE FROM {$this->Table} WHERE courseId = ? AND userId = ? AND lessonId = ?";
        return $this->getConnection()->executeUpdate($sql, array($courseId,$userId,$lessonId));
    }

}