<?php

namespace Biz\Course\Dao;

interface CourseChapterDao
{
    public function findChaptersByCourseId($courseId);

    public function getChapterCountByCourseIdAndType($courseId, $type);

    public function getChapterCountByCourseIdAndTypeAndParentId($courseId, $type, $parentId);

    public function getLastChapterByCourseIdAndType($courseId, $type);

    public function getLastChapterByCourseId($courseId);

    public function getChapterMaxSeqByCourseId($courseId);

    public function deleteChaptersByCourseId($courseId);

    public function findChaptersByCopyIdAndLockedCourseIds($pId, $courseIds);
}
