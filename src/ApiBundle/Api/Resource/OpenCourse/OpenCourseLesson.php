<?php

namespace ApiBundle\Api\Resource\OpenCourse;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Common\CommonException;
use Biz\Course\LiveReplayException;
use Biz\Course\Service\LiveReplayService;
use Biz\Live\Service\LiveService;
use Biz\OpenCourse\OpenCourseException;
use Biz\OpenCourse\Service\LiveCourseService;
use Biz\OpenCourse\Service\OpenCourseService;

class OpenCourseLesson extends AbstractResource
{
    public function add(ApiRequest $request, $courseId)
    {
        $openCourse = $this->getOpenCourseService()->tryManageOpenCourse($courseId);
        if ($this->getOpenCourseService()->countLessons(['courseId' => $courseId]) >= 300) {
            throw OpenCourseException::LESSON_NUM_LIMIT();
        }
        $lesson = $request->request->all();
        if (!ArrayToolkit::requireds($lesson, ['type', 'title'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        if ('liveOpen' == $lesson['type']) {
            if (!ArrayToolkit::requireds($lesson, ['startTime', 'length', 'replayEnable'])) {
                throw CommonException::ERROR_PARAMETER_MISSING();
            }
            if ($lesson['startTime'] < time()) {
                throw OpenCourseException::LIVE_START_TIME_OUTDATED();
            }
        }
        if ('replay' == $lesson['type']) {
            if (!ArrayToolkit::requireds($lesson, ['copyId', 'replayId'])) {
                throw CommonException::ERROR_PARAMETER_MISSING();
            }
            $replay = $this->getLiveReplayService()->getReplayByLessonIdAndReplayIdAndType($lesson['copyId'], $lesson['replayId'], 'live');
            if (empty($replay)) {
                throw LiveReplayException::NOTFOUND_LIVE_REPLAY();
            }
            $activity = $this->getActivityService()->getActivity($lesson['copyId'], true);
            $lesson['startTime'] = $activity['startTime'];
            $lesson['length'] = $activity['ext']['liveEndTime'] - $activity['ext']['liveStartTime'];
            $lesson['replayEnable'] = 1;
            $lesson['mediaId'] = $activity['ext']['liveId'];
            $lesson['liveProvider'] = $activity['ext']['liveProvider'];
        }
        $lesson['courseId'] = $courseId;
        $lesson = $this->getOpenCourseService()->createLesson($lesson);
        if ('liveOpen' == $lesson['type']) {
            $live = $this->getLiveCourseService()->createLiveRoom($openCourse, $lesson, ['authUrl' => '', 'jumpUrl' => '']);
            $this->getOpenCourseService()->updateLesson($courseId, $lesson['id'], ['mediaId' => $live['id'], 'liveProvider' => $live['provider']]);
        }
        if ('replay' == $lesson['type']) {
            $this->getLiveReplayService()->addReplay([
                'lessonId' => $lesson['id'],
                'courseId' => $courseId,
                'title' => $replay['title'],
                'replayId' => $replay['replayId'],
                'type' => 'liveOpen',
                'copyId' => $replay['id'],
            ]);
        }

        return ['ok' => true];
    }

    public function search(ApiRequest $request, $courseId)
    {
        return $this->getOpenCourseService()->findLessonsByCourseId($courseId);
    }

    public function get(ApiRequest $request, $courseId, $lessonId)
    {
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);
        if ('replay' == $lesson['type']) {
            $activity = $this->getActivityService()->getActivity($lesson['copyId']);
            $lesson['liveTitle'] = $activity['title'];
            $replays = $this->getLiveReplayService()->findReplayByLessonId($lessonId, 'liveOpen');
            $lesson['replayId'] = $replays[0]['replayId'];
        }

        return $lesson;
    }

    public function update(ApiRequest $request, $courseId, $lessonId)
    {
        $openCourse = $this->getOpenCourseService()->tryManageOpenCourse($courseId);
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);
        if (empty($lesson)) {
            throw OpenCourseException::NOTFOUND_LESSON();
        }
        $fields = $request->request->all();
        if ('liveOpen' == $lesson['type']) {
            $fields = ArrayToolkit::parts($fields, ['title', 'startTime', 'length', 'replayEnable']);
            if (!empty($fields['startTime']) && $fields['startTime'] < time() && $fields['startTime'] != $lesson['startTime']) {
                throw OpenCourseException::LIVE_START_TIME_OUTDATED();
            }
            $this->getLiveCourseService()->editLiveRoom($openCourse, [
                'title' => $fields['title'] ?? $lesson['title'],
                'type' => 'liveOpen',
                'mediaId' => $lesson['mediaId'],
                'liveProvider' => $lesson['liveProvider'],
                'startTime' => $fields['startTime'] ?? $lesson['startTime'],
                'length' => $fields['length'] ?? $lesson['length'],
            ], ['authUrl' => '', 'jumpUrl' => '']);
        }
        if ('replay' == $lesson['type']) {
            $fields = ArrayToolkit::parts($fields, ['title', 'copyId', 'replayId']);
            $replay = $this->getLiveReplayService()->getReplayByLessonIdAndReplayIdAndType($lesson['id'], $fields['replayId'], 'liveOpen');
            if (empty($replay)) {
                $this->getLiveReplayService()->deleteReplayByLessonId($lesson['id'], 'liveOpen');
                $replay = $this->getLiveReplayService()->getReplayByLessonIdAndReplayIdAndType($fields['copyId'], $fields['replayId'], 'live');
                if (empty($replay)) {
                    throw LiveReplayException::NOTFOUND_LIVE_REPLAY();
                }
                $this->getLiveReplayService()->addReplay([
                    'lessonId' => $lesson['id'],
                    'courseId' => $courseId,
                    'title' => $replay['title'],
                    'replayId' => $replay['replayId'],
                    'type' => 'liveOpen',
                    'copyId' => $replay['id'],
                ]);
                $activity = $this->getActivityService()->getActivity($fields['copyId'], true);
                $fields['startTime'] = $activity['startTime'];
                $fields['length'] = $activity['ext']['liveEndTime'] - $activity['ext']['liveStartTime'];
                $fields['mediaId'] = $activity['ext']['liveId'];
                $fields['liveProvider'] = $activity['ext']['liveProvider'];
            }
        }
        $this->getOpenCourseService()->updateLesson($courseId, $lessonId, $fields);

        return ['ok' => true];
    }

    public function remove(ApiRequest $request, $courseId, $lessonId)
    {
        $this->getOpenCourseService()->tryManageOpenCourse($courseId);
        $lesson = $this->getOpenCourseService()->getLesson($lessonId);
        if (!empty($lesson) && $courseId != $lesson['courseId']) {
            throw CommonException::ERROR_PARAMETER();
        }
        if ('published' == $lesson['status']) {
            throw OpenCourseException::DELETE_PUBLISHED_LESSON();
        }
        if ('liveOpen' == $lesson['type']) {
            $this->getLiveService()->deleteLiveRoom($lesson['mediaId']);
        }
        $this->getOpenCourseService()->deleteLesson($lessonId);

        return ['ok' => true];
    }

    /**
     * @return OpenCourseService
     */
    private function getOpenCourseService()
    {
        return $this->service('OpenCourse:OpenCourseService');
    }

    /**
     * @return LiveCourseService
     */
    private function getLiveCourseService()
    {
        return $this->service('OpenCourse:LiveCourseService');
    }

    /**
     * @return LiveService
     */
    private function getLiveService()
    {
        return $this->service('Live:LiveService');
    }

    /**
     * @return LiveReplayService
     */
    private function getLiveReplayService()
    {
        return $this->service('Course:LiveReplayService');
    }

    /**
     * @return ActivityService
     */
    private function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }
}
