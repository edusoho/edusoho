<?php
namespace Topxia\Service\Course;

interface NoteService
{
    const NOTE_TYPE_PRIVATE = 0;
    const NOTE_TYPE_PUBLIC = 1;

    public function getNote($id);

    public function getUserLessonNote($userId, $lessonId);

    public function findUserCourseNotes($userId, $courseId);

    public function searchNotes($conditions, $sort, $start, $limit);

    public function searchNoteCount($conditions);

    public function saveNote(array $note);

    public function deleteNote($id);

    public function deleteNotes(array $ids);

    public function count($id, $field, $diff);

    public function like($noteId);

    public function cancelLike($noteId);

    public function getNoteLikeByNoteIdAndUserId($noteId, $userId);

    public function findNoteLikesByUserId($userId);

    public function findNoteLikesByNoteId($noteId);

    public function findNoteLikesByNoteIds(array $noteIds);

    public function findNoteLikesByNoteIdsAndUserId(array $noteIds, $userId);
}
