<?php

namespace Biz\Course\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

/**
 * 直播回放, lessonId 在type为openLive情况下是直播公开课的课时ID, type为live情况下是activity的ID
 * Interface CourseLessonReplayDao
 *
 * @package Biz\Course\Dao
 */
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
