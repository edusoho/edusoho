<?php
namespace Topxia\Service\Course\Dao;

interface CourseNoteLikeDao
{
    public function getNoteLike($id);

    public function getNoteLikeByNoteIdAndUserId($noteId, $userId);

    public function addNoteLike($noteLike);

    public function deleteNoteLikeByNoteIdAndUserId($noteId, $userId);

    public function findNoteLikesByUserId($userId);

    public function findNoteLikesByNoteId($noteId);

    public function findNoteLikesByNoteIds(array $noteIds);

    public function findNoteLikesByNoteIdsAndUserId(array $noteIds, $userId);

    public function deleteNoteLikesByNoteId($noteId);
}
