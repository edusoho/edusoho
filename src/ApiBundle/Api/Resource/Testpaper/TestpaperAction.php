<?php

namespace ApiBundle\Api\Resource\Testpaper;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Course\Service\CourseService;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TestpaperAction extends AbstractResource
{
    /**
     * @param $request
     * @param $id testId testResultId
     * action "do/redo"
     * post params: targetType targetId
     */
    public function add(ApiRequest $request, $id)
    {
        $action = $request->request->get('action');
        $testpaper = $this->getTestpaperService()->getTestpaper($id);
        $method = $action.'Testpaper';
        if (!method_exists($this, $method)) {
            throw new \BadMethodCallException(sprintf('Unknown property "%s" on TestpaperAction "%s".', $action, get_class($this)));
        }

        return $this->$method($request, $testpaper);
    }

    protected function doTestpaper(ApiRequest $request, $testpaper)
    {
        $targetType = $request->request->get('targetType'); // => task
        $targetId = $request->request->get('targetId'); // => taskId

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw new AccessDeniedHttpException('您尚未登录，不能查看该课时');
        }

        $task = $this->getTaskService()->getTask($targetId);
        if (!$task) {
            throw new NotFoundHttpException('试卷所属课时不存在！');
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);

        if (empty($course)) {
            throw new NotFoundHttpException('试卷所属课程不存在！');
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            throw new AccessDeniedHttpException('不是试卷所属课程老师或学生');
        }

        if (empty($testpaper)) {
            throw new NotFoundHttpException('试卷不存在！或已删除!');
        }

        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $task['activity'] = $activity;
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        $testpaperResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $testpaperActivity['mediaId'], $activity['fromCourseId'], $activity['id'], $activity['mediaType']);

        $items = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);
        $testpaper['metas']['question_type_seq'] = array_keys($items);
        if (empty($testpaperResult)) {
            if ('draft' == $testpaper['status']) {
                throw new AccessDeniedHttpException('该试卷未发布，如有疑问请联系老师！!');
            }
            if ('closed' == $testpaper['status']) {
                throw new AccessDeniedHttpException('该试卷已关闭，如有疑问请联系老师！!');
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
            throw new AccessDeniedHttpException('试卷正在批阅！不能重新考试!');
        }
    }

    protected function redoTestpaper(ApiRequest $request, $testpaper)
    {
        $targetType = $request->request->get('targetType'); // => task
        $targetId = $request->request->get('targetId'); // => taskId

        $user = $this->getCurrentUser();
        if (!$user->isLogin()) {
            throw new AccessDeniedHttpException('您尚未登录，不能查看该课时');
        }

        $task = $this->getTaskService()->getTask($targetId);
        if (!$task) {
            throw new NotFoundHttpException('试卷所属课时不存在！');
        }

        if (empty($testpaper)) {
            throw new NotFoundHttpException('试卷不存在！或已删除!');
        }

        if ('draft' == $testpaper['status']) {
            throw new NotFoundHttpException('该试卷未发布，如有疑问请联系老师！!');
        }

        if ('closed' == $testpaper['status']) {
            throw new NotFoundHttpException('该试卷已关闭，如有疑问请联系老师！!');
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);

        if (empty($course)) {
            throw new NotFoundHttpException('试卷所属课程不存在！');
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            throw new AccessDeniedHttpException('不是试卷所属课程老师或学生');
        }

        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $task['activity'] = $activity;
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        $testpaperResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $testpaper['id'], $activity['fromCourseSetId'], $activity['id'], $testpaper['type']);

        if ($testpaperActivity['doTimes'] && $testpaperResult && 'finished' == $testpaperResult['status']) {
            throw new AccessDeniedHttpException('该试卷只能考一次，不能再考！');
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
