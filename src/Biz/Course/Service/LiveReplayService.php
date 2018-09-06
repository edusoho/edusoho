<?php

namespace Biz\Course\Service;

use Biz\System\Annotation\Log;

/**
 * 直播回放, lessonId 在type为liveOpen情况下是直播公开课的课时ID, type为live情况下是课程的activity的ID.
 *
 * Interface LiveReplayService
 */
interface LiveReplayService
{
    const REPLAY_UNGENERATE_STATUS = 'ungenerated';
    const REPLAY_GENERATING_STATUS = 'generating';
    const REPLAY_VIDEO_GENERATE_STATUS = 'videoGenerated';
    const REPLAY_GENERATE_STATUS = 'generated';

    /**
     * @before getCourseLessonReplay
     *
     * @param int $id
     *
     * @return array
     */
    public function getReplay($id);

    /**
     * @before getCourseLessonReplayByLessonId
     *
     * @param int    $lessonId
     * @param string $lessonType
     *
     * @return array
     */
    public function findReplayByLessonId($lessonId, $lessonType = 'live');

    /**
     * @before addCourseLessonReplay
     *
     * @param array $replay
     *
     * @return array
     */
    public function addReplay($replay);

    /**
     * @before deleteLessonReplayByLessonId
     * @before deleteCourseLessonReplayByLessonId
     *
     * @param int    $lessonId
     * @param string $lessonType
     *
     * @return bool
     */
    public function deleteReplayByLessonId($lessonId, $lessonType = 'live');

    public function deleteReplaysByCourseId($courseId, $lessonType = 'live');

    /**
     * @before updateCourseLessonReplay
     *
     * @param int   $id
     * @param array $fields
     *
     * @return replay
     */
    public function updateReplay($id, $fields);

    /**
     * @before updateCourseLessonReplayByLessonId
     *
     * @param int    $lessonId
     * @param array  $fields
     * @param string $lessonType
     *
     * @return array
     */
    public function updateReplayByLessonId($lessonId, $fields, $lessonType = 'live');

    /**
     * @before searchCourseLessonReplayCount
     *
     * @param array $conditions
     *
     * @return int
     */
    public function searchCount($conditions);

    /**
     * searchCourseLessonReplays.
     *
     * @param array $conditions
     * @param array $orderBy
     * @param int   $start
     * @param int   $limit
     *
     * @return array[]
     */
    public function searchReplays($conditions, $orderBy, $start, $limit);

    public function findReplaysByCourseIdAndLessonId($courseId, $lessonId, $lessonType = 'live');

    public function entryReplay($replayId, $liveId, $liveProvider, $ssl = false);

    public function updateReplayShow($showReplayIds, $lessonId);

    /**
     * @param $liveId
     * @param $courseId
     * @param $lessonId
     * @param $liveProvider
     * @param $type
     *
     * @return mixed
     * @Log(module="live",action="generate_live_replay",funcName="findReplaysByCourseIdAndLessonId",param="courseId,liveId,type")
     */
    public function generateReplay($liveId, $courseId, $lessonId, $liveProvider, $type);
}
