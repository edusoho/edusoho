<?php

namespace ApiBundle\Api\Resource\Course;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Activity\LiveActivityException;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\LessonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\LessonService;
use Biz\Util\EdusohoLiveClient;

class CourseLiveTask extends AbstractResource
{
    const DAY_TIME = 86400;

    const WEEK_TIME = 86400 * 7;

    const WEEKDAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    public function add(ApiRequest $request, $courseId)
    {
        if (!$this->getCourseService()->hasCourseManagerRole($courseId, 'course_lesson_manage')) {
            throw CourseException::FORBIDDEN_MANAGE_COURSE();
        }

        $data = $request->request->all();
        if (!ArrayToolkit::requireds($data, ['startDate', 'length', 'title'], true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        if (time() > strtotime($data['startDate']) || $data['length'] <= 0) {
            throw LiveActivityException::LIVE_TIME_INVALID();
        }
        $liveAccount = (new EdusohoLiveClient())->getLiveAccount();
        if (EdusohoLiveClient::LIVE_PROVIDER_QUANSHI == $liveAccount['provider']) {
            $data['roomType'] = EdusohoLiveClient::LIVE_ROOM_SMALL;
        }

        $start = $request->request->get('start', 0);
        $limit = $request->request->get('limit', 5);

        $repeatType = $request->request->get('repeatType', '');
        switch ($repeatType) {
            case 'day':
                $result = $this->batchAddLiveTaskWithDayRepeat($courseId, $data, $start, $limit);
                break;
            case 'week':
                $result = $this->batchAddLiveTaskWithWeekRepeat($courseId, $data, $start, $limit);
                break;
            default:
                $result = $this->addLiveTask($courseId, $data);
                break;
        }

        return $result;
    }

    protected function addLiveTask($courseId, $data)
    {
        if (!$this->canCreateLesson($courseId, 1)) {
            throw LessonException::LESSON_NUM_LIMIT();
        }

        list($course) = $this->getCourseService()->tryTakeCourse($courseId);

        $lesson = [
            'fromUserId' => $this->getCurrentUser()->getId(),
            'mediaType' => 'live',
            'fromCourseId' => $courseId,
            'fromCourseSetId' => $course['courseSetId'],
            'title' => $data['title'],
            'startTime' => $data['startDate'],
            'length' => $data['length'],
            'finishType' => 'join',
            'finishData' => '',
            'roomType' => $data['roomType'] ?? '',
        ];

        list($lesson, $task) = $this->getLessonService()->createLesson($lesson);
        $lesson = $this->getLessonService()->publishLesson($courseId, $lesson['id']);

        $lesson['task'] = $task;

        return $this->makePagingObject([$lesson], 1, 0, 1);
    }

    protected function batchAddLiveTaskWithWeekRepeat($courseId, $data, $start, $limit)
    {
        if (!ArrayToolkit::requireds($data, ['repeatData', 'taskNum'], true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        if (!is_array($data['repeatData'])) {
            throw CommonException::ERROR_PARAMETER();
        }

        if (!$this->canCreateLesson($courseId, $data['taskNum'])) {
            throw LessonException::LESSON_NUM_LIMIT();
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        $user = $this->getCurrentUser();

        $repeatCount = count($data['repeatData']);
        $todayWeek = ucfirst(date('l'));
        $dateTime = strtotime($data['startDate']);
        $clock = date('H:i', strtotime($data['startDate']));

        $lessons = [];
        $limitTaskNum = ($start + $limit) > $data['taskNum'] ? $data['taskNum'] : ($start + $limit);
        for ($num = $start; $num < $limitTaskNum; ++$num) {
            $key = $num % $repeatCount;
            $weekDiff = floor($num / $repeatCount);
            $weekDay = ucfirst($data['repeatData'][$key]);

            if ($todayWeek === $weekDay && date('Y-m-d') == date('Y-m-d', $dateTime)) {
                $startTime = $dateTime + $weekDiff * self::WEEK_TIME;
            } else {
                $weekTime = strtotime(date('Y-m-d', strtotime($weekDay, $dateTime)).' '.$clock);
                $startTime = $weekDiff * self::WEEK_TIME + $weekTime;
            }

            $lessonFields = [
                'fromUserId' => $user['id'],
                'mediaType' => 'live',
                'fromCourseId' => $courseId,
                'fromCourseSetId' => $course['courseSetId'],
                'title' => $data['title'] . ' ' . ($num + 1),
                'startTime' => date('Y-m-d H:i', $startTime),
                'length' => $data['length'],
                'finishType' => 'join',
                'finishData' => '',
                'roomType' => $data['roomType'] ?? '',
            ];
            try {
                $this->biz['db']->beginTransaction();
                list($lesson, $task) = $this->getLessonService()->createLesson($lessonFields);
                $lesson = $this->getLessonService()->publishLesson($courseId, $lesson['id']);
                $lesson['task'] = $task;
                $this->biz['db']->commit();
            } catch (\Exception $e) {
                $this->biz['db']->rollback();
                $lesson = [];
            }

            if (!empty($lesson)) {
                $lessons[] = $lesson;
            }
        }

        return $this->makePagingObject($lessons, $data['taskNum'], $start, $limit);
    }

    protected function batchAddLiveTaskWithDayRepeat($courseId, $data, $start, $limit)
    {
        if (!ArrayToolkit::requireds($data, ['repeatData', 'taskNum'], true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        if (is_array($data['repeatData'])) {
            throw CommonException::ERROR_PARAMETER();
        }

        if (!$this->canCreateLesson($courseId, $data['taskNum'])) {
            throw LessonException::LESSON_NUM_LIMIT();
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($courseId);
        $user = $this->getCurrentUser();
        $dateTime = strtotime($data['startDate']);

        $lessons = [];
        $limitTaskNum = ($start + $limit) > $data['taskNum'] ? $data['taskNum'] : ($start + $limit);
        for ($num = $start; $num < $limitTaskNum; ++$num) {
            $startTime = $dateTime + $data['repeatData'] * self::DAY_TIME * $num;
            $lesson = [
                'fromUserId' => $user['id'],
                'mediaType' => 'live',
                'fromCourseId' => $courseId,
                'fromCourseSetId' => $course['courseSetId'],
                'title' => $data['title'] . ' ' . ($num + 1),
                'startTime' => date('Y-m-d H:i', $startTime),
                'length' => $data['length'],
                'finishType' => 'join',
                'finishData' => '',
                'roomType' => $data['roomType'] ?? '',
            ];

            try {
                $this->biz['db']->beginTransaction();
                list($lesson, $task) = $this->getLessonService()->createLesson($lesson);
                $lesson = $this->getLessonService()->publishLesson($courseId, $lesson['id']);
                $lesson['task'] = $task;
                $this->biz['db']->commit();
            } catch (\Exception $e) {
                $this->biz['db']->rollback();
                $lesson = [];
            }

            if (!empty($lesson)) {
                $lessons[] = $lesson;
            }
        }

        return $this->makePagingObject($lessons, $data['taskNum'], $start, $limit);
    }

    protected function canCreateLesson($courseId, $addNum)
    {
        $lessonCount = $this->getLessonService()->countLessons(['courseId' => $courseId]);
        $lessonLimitNum = $this->getLessonService()->getLessonLimitNum();
        if ($lessonLimitNum < $lessonCount + $addNum) {
            return false;
        }

        return true;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return LessonService
     */
    protected function getLessonService()
    {
        return $this->service('Course:LessonService');
    }
}
