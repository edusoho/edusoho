<?php

namespace ApiBundle\Api\Resource\Testpaper;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Testpaper\TestpaperException;
use Biz\User\UserException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TestpaperAction extends AbstractResource
{
    /**
     * @param $request
     * @param $id
     * action "do/redo"
     * post params: targetType targetId
     */
    public function add(ApiRequest $request, $id)
    {
        $action = $request->request->get('action');
        $testpaper = $this->getTestpaperService()->getTestpaper($id);
        $method = $action.'Testpaper';
        if (!method_exists($this, $method)) {
            throw CommonException::NOTFOUND_METHOD();
        }

        return $this->$method($request, $testpaper);
    }

    protected function doTestpaper(ApiRequest $request, $testpaper)
    {
        $targetType = $request->request->get('targetType'); // => task
        $targetId = $request->request->get('targetId'); // => taskId

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw UserException::UN_LOGIN();
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

        if (empty($testpaper)) {
            throw TestpaperException::NOTFOUND_TESTPAPER();
        }

        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $task['activity'] = $activity;
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        $testpaperResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $testpaperActivity['mediaId'], $activity['fromCourseId'], $activity['id'], $activity['mediaType']);

        $items = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);
        $testpaper['metas']['question_type_seq'] = array_keys($items);
        if (empty($testpaperResult)) {
            if ('draft' == $testpaper['status']) {
                throw TestpaperException::DRAFT_TESTPAPER();
            }
            if ('closed' == $testpaper['status']) {
                throw TestpaperException::CLOSED_TESTPAPER();
            }

            $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], array('lessonId' => $activity['id'], 'courseId' => $activity['fromCourseId'], 'limitedTime' => $testpaperActivity['limitedTime']));

            return array(
                'testpaperResult' => $testpaperResult,
                'testpaper' => $testpaper,
                'items' => $items,
                'isShowTestResult' => 1,
            );
        }
        if (in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return array(
                'testpaperResult' => $testpaperResult,
                'testpaper' => $testpaper,
                'items' => $items,
                'isShowTestResult' => 1,
            );
        } else {
            throw TestpaperException::REVIEWING_TESTPAPER();
        }
    }

    protected function redoTestpaper(ApiRequest $request, $testpaper)
    {
        $targetType = $request->request->get('targetType'); // => task
        $targetId = $request->request->get('targetId'); // => taskId

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw UserException::UN_LOGIN();
        }

        $task = $this->getTaskService()->getTask($targetId);
        if (!$task) {
            throw TaskException::NOTFOUND_TASK();
        }

        if (empty($testpaper)) {
            throw TestpaperException::NOTFOUND_TESTPAPER();
        }

        if ('draft' == $testpaper['status']) {
            throw TestpaperException::DRAFT_TESTPAPER();
        }

        if ('closed' == $testpaper['status']) {
            throw TestpaperException::CLOSED_TESTPAPER();
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);

        if (empty($course)) {
            throw CourseException::NOTFOUND_COURSE();
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            throw CourseException::FORBIDDEN_TAKE_COURSE();
        }

        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $task['activity'] = $activity;
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        $testpaperResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $testpaper['id'], $activity['fromCourseSetId'], $activity['id'], $testpaper['type']);

        if ($testpaperActivity['doTimes'] && $testpaperResult && 'finished' == $testpaperResult['status']) {
            throw TestpaperException::FORBIDDEN_RESIT();
        } elseif ($testpaperActivity['redoInterval']) {
            $nextDoTime = $testpaperResult['checkedTime'] + $testpaperActivity['redoInterval'] * 3600;
            if ($nextDoTime > time()) {
                throw new AccessDeniedHttpException('教师设置了重考间隔，请在'.date('Y-m-d H:i:s', $nextDoTime).'之后再考！');
            }
        }

        if (!$testpaperResult || ($testpaperResult && 'finished' == $testpaperResult['status'])) {
            $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], array('lessonId' => $activity['id'], 'courseId' => $activity['fromCourseId'], 'limitedTime' => $testpaperActivity['limitedTime']));
        }

        $items = $this->getTestpaperService()->showTestpaperItems($testpaper['id'], $testpaperResult['id']);
        $testpaper['metas']['question_type_seq'] = array_keys($items);

        return array(
            'testpaperResult' => $testpaperResult,
            'testpaper' => $testpaper,
            'items' => $items,
            'isShowTestResult' => 0,
        );
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
