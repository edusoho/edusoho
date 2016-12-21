<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface CourseLessonReplayDao extends GeneralDaoInterface
{
    public function deleteByLessonId($lessonId, $lessonType);

    /**
     * @param $lessonId
     * @param $lessonType
     * @return mixed
     * @deprecated  getByLessonId
     */
    public function findByLessonId($lessonId, $lessonType);

    public function deleteByCourseId($courseId, $lessonType);

    public function getByCourseIdAndLessonId($courseId, $lessonId, $lessonType);

    public function updateByLessonId($lessonId, $fields, $lessonType);

    public function findByCourseIdAndLessonId($courseId, $lessonId, $lessonType = 'live');

}
