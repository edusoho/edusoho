<?php
namespace Topxia\Service\Course;

interface NoteService
{
    CONST NOTE_TYPE_PRIVATE = 0;
    CONST NOTE_TYPE_PUBLIC = 1;

	public function getNote($id);

	public function addNote($note);

    public function updateNote($id,$note);

    public function saveNote(array $note);

	public function deleteNote($id);

    public function findUserLessonNotes($userId,$lessonId);

	public function deleteNotes($ids);

    public function searchNotes($conditions, $sort, $start, $limit);

    public function searchNotesCount($conditions);

    public function findUserCourseNotes($userId, $courseId);

}