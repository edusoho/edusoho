<?php

namespace ApiBundle\Api\Resource\Exercise;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Course\CourseException;
use Biz\Testpaper\ExerciseException;
use Biz\Task\TaskException;
use Biz\Task\Service\TaskService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Activity\Service\ActivityService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Course\Service\CourseService;
use AppBundle\Common\ArrayToolkit;

class ExerciseResult extends AbstractResource
{
    public function add(ApiRequest $request, $exerciseId)
    {
        $user = $this->getCurrentUser();

        $targetType = $request->request->get('targetType');
        $targetId = $request->request->get('targetId');

        $exercise = $this->getTestpaperService()->getTestpaper($exerciseId);
        if (empty($exercise) || 'exercise' != $exercise['type']) {
            throw ExerciseException::NOTFOUND_EXERCISE();
        }

        $task = $this->getTaskService()->getTask($targetId);
        if (empty($task) || 'exercise' != $task['type']) {
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

        $exerciseResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $activity['mediaId'], $activity['fromCourseId'], $activity['id'], $activity['mediaType']);

        if (empty($exerciseResult) || 'finished' == $exerciseResult['status']) {
            if ('draft' == $exercise['status']) {
                throw ExerciseException::DRAFT_EXERCISE();
            }
            if ('closed' == $exercise['status']) {
                throw ExerciseException::CLOSED_EXERCISE();
            }

            $exerciseResult = $this->getTestpaperService()->startTestpaper($exercise['id'], array('lessonId' => $activity['id'], 'courseId' => $activity['fromCourseId'], 'limitedTime' => $exercise['limitedTime']));
            $exerciseResult['items'] = array_values($this->getTestpaperService()->showTestpaperItems($exercise['id']));

            $this->updateOrders($exerciseResult['id'], $exerciseResult['items']);
        } else {
            $exerciseResult['items'] = array_values($this->getTestpaperService()->showTestpaperItems($exercise['id'], $exerciseResult['id']));
        }

        return $exerciseResult;
    }

    protected function updateOrders($exerciseResultId, $items)
    {
        $orders = array();
        foreach ($items as $item) {
            $orders[] = $item['id'];
            if (!empty($item['subs'])) {
                foreach ($item['subs'] as $subItem) {
                    $orders[] = $subItem['id'];
                }
            }
        }
        $this->getTestpaperService()->updateTestpaperResult($exerciseResultId, array('metas' => array('orders' => $orders)));
    }

    public function update(ApiRequest $request, $exerciseId, $exerciseResultId)
    {
        $user = $this->getCurrentUser();

        $data = $request->request->all();
        $exerciseResult = $this->getTestpaperService()->getTestpaperResult($exerciseResultId);

        if (!empty($exerciseResult) && !in_array($exerciseResult['status'], array('doing', 'paused'))) {
            throw ExerciseException::FORBIDDEN_DUPLICATE_COMMIT();
        }

        if (!empty($exerciseResult['metas']['orders'])) {
            $data['seq'] = $exerciseResult['metas']['orders'];
        }

        $exerciseResult = $this->getTestpaperService()->finishTest($exerciseResult['id'], $data);
        $exercise = $this->getTestpaperService()->getTestpaper($exerciseResult['testId']);

        if ($exerciseResult['userId'] != $user['id']) {
            $course = $this->getCourseService()->tryManageCourse($exerciseResult['courseId']);
        }

        if (empty($course) && $exerciseResult['userId'] != $user['id']) {
            throw ExerciseException::FORBIDDEN_ACCESS_EXERCISE();
        }

        return $exerciseResult;
    }

    public function get(ApiRequest $request, $exerciseId, $exerciseResultId)
    {
        $user = $this->getCurrentUser();

        $exerciseResult = $this->getTestpaperService()->getTestpaperResult($exerciseResultId);
        if (empty($exerciseResult)) {
            throw ExerciseException::NOTFOUND_RESULT();
        }

        $exercise = $this->getTestpaperService()->getTestpaper($exerciseId);
        if (empty($exercise) || 'exercise' != $exercise['type']) {
            throw ExerciseException::NOTFOUND_EXERCISE();
        }

        $canTakeCourse = $this->getCourseService()->canTakeCourse($exercise['courseId']);
        if (!$canTakeCourse) {
            throw CourseException::FORBIDDEN_TAKE_COURSE();
        }

        $canCheckExercise = $this->getTestpaperService()->canLookTestpaper($exerciseResult['id']);
        if (empty($user) || (!$canCheckExercise && $exerciseResult['userId'] != $user['id'])) {
            throw ExerciseException::FORBIDDEN_ACCESS_EXERCISE();
        }

        $exerciseResult['items'] = array_values($this->getTestpaperService()->showTestpaperItems($exercise['id'], $exerciseResultId));
        $exerciseResult['items'] = $this->fillItems($exerciseResult['items'], $exerciseResult);
        $exerciseResult['rightRate'] = $this->getRightRate($exerciseResult['items']);

        return $exerciseResult;
    }

    protected function getRightRate($items)
    {
        $subjectivityNum = $rightNum = $num = 0;

        foreach ($items as $item) {
            ++$num;

            if (isset($item['testResult']) && 'right' == $item['testResult']['status']) {
                ++$rightNum;
            }

            if ('essay' == $item['type']) {
                ++$subjectivityNum;
            }

            if ('material' == $item['type'] && !empty($item['subs'])) {
                --$num;
                foreach ($item['subs'] as $subItem) {
                    ++$num;

                    if ('essay' == $subItem['type']) {
                        ++$subjectivityNum;
                    }

                    if (isset($subItem['testResult']) && 'right' == $subItem['testResult']['status']) {
                        ++$rightNum;
                    }
                }
            }
        }

        return intval($rightNum / ($num - $subjectivityNum) * 100 + 0.5);
    }

    protected function fillItems($items, $exerciseResult)
    {
        $itemSetResults = $this->getTestpaperService()->findItemResultsByResultId($exerciseResult['id']);
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
