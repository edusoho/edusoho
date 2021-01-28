<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface CourseChapterDao extends AdvancedDaoInterface
{
    public function getByCopyIdAndLockedCourseId($copyId, $courseId);

    public function findByCopyId($copyId);

    public function findChaptersByCourseId($courseId);

    public function findLessonsByCourseId($courseId);

    public function getChapterCountByCourseIdAndType($courseId, $type);

    public function getLastChapterByCourseIdAndType($courseId, $type);

    public function getLastChapterByCourseId($courseId);

    public function getChapterMaxSeqByCourseId($courseId);

    public function deleteChaptersByCourseId($courseId);

    public function findChaptersByCopyIdAndLockedCourseIds($pId, $courseIds);

    public function findByCopyIdsAndLockedCourseIds($copyIds, $courseIds);
}
