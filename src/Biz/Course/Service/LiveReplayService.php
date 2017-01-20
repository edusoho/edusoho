<?php

namespace Biz\Course\Service;

/**
 * 直播回放, lessonId 在type为liveOpen情况下是直播公开课的课时ID, type为live情况下是课程的activity的ID
 *
 * Interface LiveReplayService
 * @package Biz\Course\Service
 */
interface LiveReplayService
{
    const REPLAY_UNGENERATE_STATUS     = 'ungenerated';
    const REPLAY_GENERATING_STATUS     = 'generating';
    const REPLAY_VIDEO_GENERATE_STATUS = 'videoGenerated';
    const REPLAY_GENERATE_STATUS       = 'generated';

    /**
     * @before getCourseLessonReplay
     *
     * @param  integer $id
     *
     * @return replay
     */
    public function getReplay($id);

    /**
     * @before getCourseLessonReplayByLessonId
     *
     * @param  integer $lessonId
     * @param  string  $lessonType
     *
     * @return array
     */
    public function findReplayByLessonId($lessonId, $lessonType = 'live');

    /**
     * @before addCourseLessonReplay
     *
     * @param  array $replay
     *
     * @return array
     */
    public function addReplay($replay);

    /**
     * @before deleteLessonReplayByLessonId
     * @before deleteCourseLessonReplayByLessonId
     *
     * @param  integer $lessonId
     * @param  string  $lessonType
     *
     * @return bool
     */
    public function deleteReplayByLessonId($lessonId, $lessonType = 'live');

    public function deleteReplaysByCourseId($courseId, $lessonType = 'live');

    /**
     * @before updateCourseLessonReplay
     *
     * @param  integer $id
     * @param  array   $fields
     *
     * @return replay
     */
    public function updateReplay($id, $fields);

    /**
     * @before updateCourseLessonReplayByLessonId
     *
     * @param  integer $lessonId
     * @param  array   $fields
     * @param  string  $lessonType
     *
     * @return replay
     */
    public function updateReplayByLessonId($lessonId, $fields, $lessonType = 'live');

    /**
     * @before searchCourseLessonReplayCount
     *
     * @param  array $conditions
     *
     * @return replays
     */
    public function searchCount($conditions);

    /**
     * searchCourseLessonReplays
     *
     * @param  array   $conditions
     * @param  array   $orderBy
     * @param  integer $start
     * @param  integer $limit
     *
     * @return replays
     */
    public function searchReplays($conditions, $orderBy, $start, $limit);

    public function findReplaysByCourseIdAndLessonId($courseId, $lessonId, $lessonType = 'live');

    public function entryReplay($replayId, $liveId, $liveProvider, $ssl = false);

    public function updateReplayShow($showReplayIds, $lessonId);

    public function generateReplay($liveId, $courseId, $lessonId, $liveProvider, $type);

}
