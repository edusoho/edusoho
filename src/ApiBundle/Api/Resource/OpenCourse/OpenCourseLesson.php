<?php

namespace ApiBundle\Api\Resource\OpenCourse;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
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
        $lesson['courseId'] = $courseId;
        $lesson = $this->getOpenCourseService()->createLesson($lesson);
        if ('liveOpen' == $lesson['type']) {
            $live = $this->getLiveCourseService()->createLiveRoom($openCourse, $lesson, ['authUrl' => '', 'jumpUrl' => '']);
            $this->getOpenCourseService()->updateLesson($courseId, $lesson['id'], ['mediaId' => $live['id'], 'liveProvider' => $live['provider']]);
        }

        return ['ok' => true];
    }

    public function search(ApiRequest $request, $courseId)
    {
        return $this->getOpenCourseService()->findLessonsByCourseId($courseId);
    }

    public function get(ApiRequest $request, $courseId, $lessonId)
    {
        return $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);
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
}
