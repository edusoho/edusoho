<?php

namespace Topxia\Service\Course\Dao;

interface CourseChapterDao
{

    public function getChapter($id);

    public function findChaptersByCourseId($courseId);

    public function getChapterCountByCourseId($courseId);

    public function getChapterMaxSeqByCourseId($courseId);

    public function addChapter(array $chapter);

    public function updateChapter($id, array $chapter);

    public function deleteChapter($id);

    public function deleteChaptersByCourseId($courseId);

}