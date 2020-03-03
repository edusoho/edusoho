<?php

namespace Biz\OpenCourse\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface OpenCourseLessonDao extends GeneralDaoInterface
{
    public function findByIds(array $ids);

    public function findByCourseId($courseId);

    public function deleteByCourseId($id);

    public function searchLessonsWithOrderBy($conditions, $start, $limit);

    public function getLessonMaxSeqByCourseId($courseId);

    public function findTimeSlotOccupiedLessonsByCourseId($courseId, $startTime, $endTime, $excludeLessonId);

    public function findFinishedLivesWithinTwoHours();
}
