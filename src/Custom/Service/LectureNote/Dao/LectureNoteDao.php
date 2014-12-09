<?php
namespace Custom\Service\LectureNote\Dao;

interface LectureNoteDao
{
    public function getLectureNote($id);

    public function addLectureNote(array $lectureNote);

    public function findAllLectureNotes();

    public function deleteLectureNote($id);

    public function findLectureNotesByLessonId($lessonId);

    public function findLectureNotesByType($type);
}