<?php

namespace Topxia\Service\Course\Dao\Impl;

use Topxia\Service\Common\BaseDao;
use Topxia\Service\Course\Dao\DraftDao;

class DraftDaoImpl extends BaseDao implements DraftDao
{
        protected $draftTable = 'course_draft';

        public function getDraft($id)
    {
        $sql = "SELECT * FROM {$this->draftTable} WHERE id = ? LIMIT 1";
        return  $draft = $this->getConnection()->fetchAssoc($sql, array($id)) ? : null;
    }

    public function getEditDrafts($courseId,$userId,$lessonId)
    {
        $sql = "SELECT * FROM {$this->draftTable} WHERE courseId = ? AND userId = ? AND lessonId = ?";
        return $this->getConnection()->fetchAssoc($sql, array($courseId,$userId,$lessonId)) ? : null;
    }

    public function addDraft($draft)
    {
        $affected = $this->getConnection()->insert($this->draftTable, $draft);
        if ($affected <= 0) {
            throw $this->createDaoException('Insert draft error.');
        }
        return $this->getDraft($this->getConnection()->lastInsertId());
    }

    public function updateEditDraft($userId,$courseId,$lessonId,$fields)
     {
        $this->getConnection()->update($this->draftTable, $fields, array('courseId' => $courseId,'userId' => $userId,'lessonId' => $lessonId));
        return $this->getEditDrafts($courseId,$userId,$lessonId);
    }

    public function deleteDraftByCourse($courseId,$userId,$lessonId)
    {
        $sql = "DELETE FROM {$this->draftTable} WHERE courseId = ? AND userId = ? AND lessonId = ?";
        return $this->getConnection()->executeUpdate($sql, array($courseId,$userId,$lessonId));
    }

}