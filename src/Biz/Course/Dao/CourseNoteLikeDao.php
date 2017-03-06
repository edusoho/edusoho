<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseNoteLikeDao extends GeneralDaoInterface
{
    public function getByNoteIdAndUserId($noteId, $userId);

    public function deleteByNoteIdAndUserId($noteId, $userId);

    public function findByUserId($userId);

    public function findByNoteId($noteId);

    public function findByNoteIds(array $noteIds);

    public function findByNoteIdsAndUserId(array $noteIds, $userId);

    public function deleteByNoteId($noteId);
}
