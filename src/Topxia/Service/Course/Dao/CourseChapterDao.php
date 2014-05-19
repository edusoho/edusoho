<?php

namespace Topxia\Service\Course\Dao;

interface CourseChapterDao
{

    public function getChapter($id);

    public function findChaptersByCourseId($courseId);

    public function getChapterCountByCourseIdAndType($courseId, $type);

    public function getChapterCountByCourseIdAndTypeAndParentId($courseId, $type, $parentId);

    public function getLastChapterByCourseIdAndType($courseId, $type);

    public function getLastChapterByCourseId($courseId);

    public function getChapterMaxSeqByCourseId($courseId);

    public function addChapter(array $chapter);

    public function updateChapter($id, array $chapter);

    public function deleteChapter($id);

    public function deleteChaptersByCourseId($courseId);

}