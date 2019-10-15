<?php

namespace ApiBundle\Api\Resource\Homework;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseException;
use Biz\Testpaper\HomeworkException;
use Biz\Task\TaskException;
use Biz\Task\Service\TaskService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Activity\Service\ActivityService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Course\Service\CourseService;
use AppBundle\Common\ArrayToolkit;

class HomeworkResult extends AbstractResource
{
    public function add(ApiRequest $request, $homeworkId)
    {
        $user = $this->getCurrentUser();

        $targetType = $request->request->get('targetType');
        $targetId = $request->request->get('targetId');

        $homework = $this->getTestpaperService()->getTestpaper($homeworkId);
        if (empty($homework) || 'homework' != $homework['type']) {
            throw HomeworkException::NOTFOUND_HOMEWORK();
        }

        $task = $this->getTaskService()->getTask($targetId);
        if (!$task) {
            throw TaskException::NOTFOUND_TASK();
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);
        if (empty($course)) {
            throw CourseException::NOTFOUND_COURSE();
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            throw CourseException::FORBIDDEN_TAKE_COURSE();
        }

        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        $homeworkResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $activity['mediaId'], $activity['fromCourseId'], $activity['id'], $activity['mediaType']);

        if (empty($homeworkResult) || 'finished' == $homeworkResult['status']) {
            if ('draft' == $homework['status']) {
                throw HomeworkException::DRAFT_HOMEWORK();
            }
            if ('closed' == $homework['status']) {
                throw HomeworkException::CLOSED_HOMEWORK();
            }

            $homeworkResult = $this->getTestpaperService()->startTestpaper($homework['id'], array('lessonId' => $activity['id'], 'courseId' => $activity['fromCourseId'], 'limitedTime' => $homework['limitedTime']));
        } elseif ('reviewing' == $homeworkResult['status']) {
            throw HomeworkException::REVIEWING_HOMEWORK();
        }

        $homeworkResult['items'] = array_values($this->getTestpaperService()->showTestpaperItems($homework['id']));

        return $homeworkResult;
    }

    public function update(ApiRequest $request, $homeworkId, $homeworkResultId)
    {
        $user = $this->getCurrentUser();

        $data = $request->request->all();
        $homeworkResult = $this->getTestpaperService()->getTestpaperResult($homeworkResultId);

        if (!empty($homeworkResult) && !in_array($homeworkResult['status'], array('doing', 'paused'))) {
            throw HomeworkException::FORBIDDEN_DUPLICATE_COMMIT();
        }

        $homeworkResult = $this->getTestpaperService()->finishTest($homeworkResult['id'], $data);
        $homework = $this->getTestpaperService()->getTestpaper($homeworkResult['testId']);

        if ($homeworkResult['userId'] != $user['id']) {
            $course = $this->getCourseService()->tryManageCourse($homeworkResult['courseId']);
        }

        if (empty($course) && $homeworkResult['userId'] != $user['id']) {
            throw HomeworkException::FORBIDDEN_ACCESS_HOMEWORK();
        }

        return $homeworkResult;
    }

    public function get(ApiRequest $request, $homeworkId, $homeworkResultId)
    {
        $user = $this->getCurrentUser();

        $homeworkResult = $this->getTestpaperService()->getTestpaperResult($homeworkResultId);
        if (empty($homeworkResult)) {
            throw HomeworkException::NOTFOUND_RESULT();
        }

        $homework = $this->getTestpaperService()->getTestpaper($homeworkId);
        if (empty($homework)) {
            throw HomeworkException::NOTFOUND_HOMEWORK();
        }

        $canTakeCourse = $this->getCourseService()->canTakeCourse($homework['courseId']);
        if (!$canTakeCourse) {
            throw CourseException::FORBIDDEN_TAKE_COURSE();
        }

        $canCheckHomework = $this->getTestpaperService()->canLookTestpaper($homeworkResult['id']);
        if (empty($user) || (!$canCheckHomework && $homeworkResult['userId'] != $user['id'])) {
            throw HomeworkException::FORBIDDEN_ACCESS_HOMEWORK();
        }

        $homeworkResult['items'] = array_values($this->getTestpaperService()->showTestpaperItems($homework['id'], $homeworkResultId));
        $homeworkResult['items'] = $this->fillItems($homeworkResult['items'], $homeworkResult);
        $homeworkResult['rightRate'] = $this->getRightRate($homeworkResult['items']);

        return $homeworkResult;
    }

    protected function getRightRate($items)
    {
        $rightNum = $num = 0;

        foreach ($items as $item) {
            ++$num;

            if (isset($item['testResult']) && 'right' == $item['testResult']['status']) {
                ++$rightNum;
            }

            if ('material' == $item['type'] && !empty($item['subs'])) {
                --$num;
                foreach ($item['subs'] as $subItem) {
                    ++$num;

                    if (isset($subItem['testResult']) && 'right' == $subItem['testResult']['status']) {
                        ++$rightNum;
                    }
                }
            }
        }

        return intval($rightNum / $num * 100 + 0.5);
    }

    protected function fillItems($items, $homeworkResult)
    {
        $itemSetResults = $this->getTestpaperService()->findItemResultsByResultId($homeworkResult['id']);
        $itemSetResults = ArrayToolkit::index($itemSetResults, 'questionId');

        foreach ($items as &$item) {
            if (isset($item['subs'])) {
                foreach ($item['subs'] as &$sub) {
                    $sub['testResult'] = isset($itemSetResults[$sub['id']]) ? $itemSetResults[$sub['id']] : null;
                }
            } else {
                $item['testResult'] = isset($itemSetResults[$item['id']]) ? $itemSetResults[$item['id']] : null;
            }
        }

        return $items;
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->service('Activity:TestpaperActivityService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->service('Activity:ActivityService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->service('Testpaper:TestpaperService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->service('Task:TaskService');
    }
}
