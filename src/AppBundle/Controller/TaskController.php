<?php

namespace AppBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\CourseException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\LearningDataAnalysisService;
use Biz\Course\Service\MemberService;
use Biz\Task\Service\TaskResultService;
use Biz\Task\Service\TaskService;
use Biz\Task\TaskException;
use Biz\User\Service\TokenService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use AppBundle\Common\ArrayToolkit;

class TaskController extends BaseController
{
    public function showAction(Request $request, $courseId, $id)
    {
        //blank返回空页面的原因是因为firefox中iframe必须加载有效的src属性才能不出现闪烁的问题.
        $blank = $request->query->get('blank');
        if ($blank) {
            return new Response();
        }

        $preview = $request->query->get('preview');

        $user = $this->getUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('info', '请先登录', '', 3, $this->generateUrl('login'));
        }

        try {
            $task = $this->tryLearnTask($courseId, $id, (bool) $preview);
            $activity = $this->getActivityService()->getActivity($task['activityId'], true);

            if (!empty($activity['ext']) && !empty($activity['ext']['mediaId'])) {
                $media = $this->getUploadFileService()->getFile($activity['ext']['mediaId']);
            }

            $media = !empty($media) ? $media : array();
        } catch (AccessDeniedException $accessDeniedException) {
            return $this->handleAccessDeniedException($accessDeniedException, $request, $id);
        } catch (CourseException $deniedException) {
            return $this->handleAccessDeniedException($deniedException, $request, $id);
        }

        $user = $this->getCurrentUser();
        $course = $this->getCourseService()->getCourse($courseId);

        $member = $this->getCourseMemberService()->getCourseMember($courseId, $user['id']);

        if ($member['locked']) {
            return $this->redirectToRoute('my_course_show', array('id' => $courseId));
        }

        if ($this->isCourseExpired($course) && !$this->getCourseService()->hasCourseManagerRole($course['id'])) {
            return $this->redirectToRoute('course_show', array('id' => $courseId));
        }

        if (null !== $member && 'teacher' != $member['role'] && !$this->getCourseMemberService()->isMemberNonExpired(
                $course,
                $member
            )
        ) {
            return $this->redirect($this->generateUrl('my_course_show', array('id' => $courseId)));
        }

        $activityConfig = $this->getActivityConfigByTask($task);

        if (null !== $member && 'student' === $member['role'] && $activityConfig->allowTaskAutoStart($task)) {
            $this->getTaskService()->trigger(
                $task['id'],
                'start',
                array(
                    'taskId' => $task['id'],
                )
            );
        }

        $taskResult = $this->getTaskResultService()->getUserTaskResultByTaskId($id);
        if (empty($taskResult)) {
            $taskResult = array('status' => 'none');
        }

        if ('finish' == $taskResult['status']) {
            $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($courseId, $user['id']);
            $finishedRate = $progress['percent'];
        }
        list($previousTask, $nextTask) = $this->getPreviousTaskAndTaskResult($task);
        $this->freshTaskLearnStat($request, $task['id']);

        if ($course['isHideUnpublish']) {
            $chapter = $this->getCourseService()->getChapter($courseId, $task['categoryId']);
            //需要8.3.8重构
            $number = explode('-', $task['number']);
            if (array_key_exists(1, $number)) {
                $task['number'] = $chapter['published_number'].'-'.$number[1];
            }
        }

        return $this->render(
            'task/show.html.twig',
            array(
                'course' => $course,
                'member' => $member,
                'task' => $task,
                'taskResult' => $taskResult,
                'nextTask' => $nextTask,
                'previousTask' => $previousTask,
                'finishedRate' => empty($finishedRate) ? 0 : $finishedRate,
                'allowEventAutoTrigger' => $activityConfig->allowEventAutoTrigger(),
                'media' => $media,
            )
        );
    }

    protected function getPreviousTaskAndTaskResult($task)
    {
        $previousTask = $nextTask = array();
        $condition = array(
            'courseId' => $task['courseId'],
            'status' => 'published',
            'seq_LT' => $task['seq'],
        );
        $previousTasks = $this->getTaskService()->searchTasks($condition, array('seq' => 'DESC'), 0, 1);
        unset($condition['seq_LT']);
        $condition['seq_GT'] = $task['seq'];
        $nextTasks = $this->getTaskService()->searchTasks($condition, array('seq' => 'ASC'), 0, 1);

        if (!empty($previousTasks)) {
            $previousTask = array_pop($previousTasks);
        }
        if (!empty($nextTasks)) {
            $nextTask = array_pop($nextTasks);
        }

        return array($previousTask, $nextTask);
    }

    protected function getActivityConfigByTask($task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId']);

        return $this->getActivityService()->getActivityConfig($activity['mediaType']);
    }

    public function previewAction($courseId, $id)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $task = $this->getTaskService()->getTask($id);

        $user = $this->getCurrentUser();

        if (empty($task) || $task['courseId'] != $courseId) {
            return $this->createNotFoundException('task is not exist');
        }

        //课程不可购买，且任务不免费
        if (empty($task['isFree']) && empty($course['buyable']) && empty($course['tryLookable'])) {
            return $this->render('task/preview-notice-modal.html.twig', array('course' => $course));
        }

        //课程关闭
        if (!empty($courseSet['status']) && 'published' != $courseSet['status']) {
            return $this->render('task/preview-notice-modal.html.twig', array('courseSet' => $courseSet));
        }

        //教学计划关闭
        if (!empty($course['status']) && 'published' != $course['status']) {
            return $this->render('task/preview-notice-modal.html.twig', array('course' => $course));
        }

        //课时不免费并且不满足：
        // 1. 有时间限制设置
        // 2. 课时为视频课时
        // 3. 视频课时非优酷等外链视频时提示购买
        $taskCanTryLook = false;
        if ($course['tryLookable'] && 'video' == $task['type']) {
            $activity = $this->getActivityService()->getActivity($task['activityId'], true);
            if (!empty($activity['ext']) && !empty($activity['ext']['file']) && 'cloud' === $activity['ext']['file']['storage']) {
                $taskCanTryLook = true;
            }
        }

        if (empty($task['isFree']) && !$taskCanTryLook) {
            if (!$user->isLogin()) {
                $this->createNewException(UserException::UN_LOGIN());
            }
            if ($course['parentId'] > 0) {
                return $this->redirect($this->generateUrl('classroom_buy_hint', array('courseId' => $course['id'])));
            }

            return $this->forward(
                'AppBundle:Course/CourseOrder:buy',
                array('id' => $courseId),
                array('preview' => true, 'lessonId' => $task['id'])
            );
        }

        //在可预览情况下查看网站设置是否可匿名预览
        $allowAnonymousPreview = $this->setting('course.allowAnonymousPreview', 1);

        if (empty($allowAnonymousPreview) && !$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        //TODO vip 插件改造 判断用户是否为VIP
        return $this->render(
            'task/preview.html.twig',
            array(
                'course' => $course,
                'task' => $task,
                'user' => $user,
                'vipStatus' => false,
            )
        );
    }

    public function contentPreviewAction($courseId, $id)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $task = $this->getTaskService()->getTask($id);

        if (empty($task) || $task['courseId'] != $courseId) {
            $this->createNewException(TaskException::NOTFOUND_TASK());
        }

        if (!$this->canPreviewTask($task, $course)) {
            $this->createNewException(TaskException::FORBIDDEN_PREVIEW_TASK());
        }

        return $this->forward('AppBundle:Activity/Activity:preview', array('task' => $task));
    }

    private function canPreviewTask($task, $course)
    {
        if ($task['isFree']) {
            return true;
        }
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        if (empty($course['tryLookable']) || 'video' != $activity['mediaType']) {
            return false;
        }

        $file = $activity['ext']['file'];

        return !empty($file) && 'cloud' == $file['storage'];
    }

    public function qrcodeAction(Request $request, $courseId, $id)
    {
        $user = $this->getCurrentUser();

        $classroom = $this->getClassroomService()->getClassroomByCourseId($courseId);
        $params = array('courseId' => $courseId, 'id' => $id);
        if ($classroom) {
            $params['classroomId'] = $classroom['id'];
        }

        $token = $this->getTokenService()->makeToken(
            'qrcode',
            array(
                'userId' => $user['id'],
                'data' => array(
                    'url' => $this->generateUrl('course_task_show', $params, true),
                ),
                'times' => 1,
                'duration' => 3600,
            )
        );
        $url = $this->generateUrl('common_parse_qrcode', array('token' => $token['token']), true);

        $response = array(
            'img' => $this->generateUrl('common_qrcode', array('text' => $url), true),
        );

        return $this->createJsonResponse($response);
    }

    public function taskActivityAction(Request $request, $courseId, $id)
    {
        $preview = $request->query->get('preview', 0);
        $task = $this->tryLearnTask($courseId, $id, $preview);

        if (empty($preview) && 'published' != $task['status']) {
            return $this->render('task/inform.html.twig');
        }

        return $this->forward(
            'AppBundle:Activity/Activity:show',
            array(
                'task' => $task,
                'preview' => $preview,
            )
        );
    }

    public function taskPluginsAction(Request $request, $courseId, $taskId)
    {
        $preview = $request->query->get('preview', false);

        $this->tryLearnTask($courseId, $taskId);
        $toolbars = array();
        foreach ($this->get('extension.manager')->getTaskToolbars() as $toolbar) {
            $toolbar['url'] = $this->generateUrl($toolbar['action'], array(
                'courseId' => $courseId,
                'taskId' => $taskId,
                'preview' => $preview,
            ));
            $toolbar['name'] = $this->get('translator')->trans($toolbar['name']);
            $toolbars[] = $toolbar;
        }

        return $this->createJsonResponse($toolbars);
    }

    public function triggerAction(Request $request, $courseId, $id)
    {
        $eventName = 'doing';
        $data = $request->request->get('data', array());
        $data['taskId'] = $id;

        $this->getCourseService()->tryTakeCourse($courseId);

        if (!empty($data['events']) || $this->validTaskLearnStat($request, $id)) {
            $result = $this->getTaskService()->trigger($id, $eventName, $data);
            $result = $this->getTaskResultService()->updateTaskResult($result['id'], array('lastLearnTime' => isset($data['lastLearnTime']) ? $data['lastLearnTime'] : 0));
            $data['valid'] = 1;
        } else {
            $result = $this->getTaskResultService()->getUserTaskResultByTaskId($id);
            $data['valid'] = 0;
        }

        return $this->createJsonResponse(
            array(
                'result' => $result,
                'lastTime' => time(),
                'event' => $eventName,
                'data' => $data,
            )
        );
    }

    public function finishAction($courseId, $id)
    {
        $course = $this->getCourseService()->getCourse($courseId);

        if (!$course['enableFinish']) {
            $this->createNewException(TaskException::CAN_NOT_FINISH());
        }

        $task = $this->getTaskService()->getTask($id);

        if ('published' != $task['status']) {
            return $this->createMessageResponse('error', '未发布的任务无法完成');
        }
        $result = $this->getTaskService()->finishTaskResult($id);

        $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($courseId, $result['userId']);

        return $this->render(
            'task/finish-result.html.twig',
            array(
                'result' => $result,
                'task' => $task,
                'nextTask' => $this->getTaskService()->getNextTask($task['id']),
                'course' => $course,
                'finishedRate' => $progress['percent'],
            )
        );
    }

    public function taskFinishedPromptAction($courseId, $id)
    {
        list($course) = $this->getCourseService()->tryTakeCourse($courseId);
        $result = $this->getTaskService()->finishTaskResult($id);
        $task = $this->getTaskService()->getTask($id);

        $progress = $this->getLearningDataAnalysisService()->getUserLearningProgress($courseId, $result['userId']);

        return $this->render(
            'task/task-finished-prompt.html.twig',
            array(
                'result' => $result,
                'task' => $task,
                'nextTask' => $this->getTaskService()->getNextTask($task['id']),
                'course' => $course,
                'finishedRate' => $progress['percent'],
            )
        );
    }

    public function finishConditionAction($task)
    {
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        $activityConfigManage = $this->get('activity_config_manager');
        $installedActivity = $activityConfigManage->getInstalledActivity($activity['mediaType']);

        if ($activityConfigManage->isLtcActivity($activity['mediaType'])) {
            if (!empty($installedActivity['routes']['finish_tip'])) {
                $container = $this->get('activity_runtime_container');

                return $container->renderRoute($activity, 'finish_tip');
            }

            $conditions = empty($installedActivity['finish_condition']) ? array() : ArrayToolkit::index($installedActivity['finish_condition'], 'type');

            return $this->render('task/finish-tip.html.twig', array(
                'activity' => $activity,
                'conditions' => $conditions,
            ));
        }

        $config = $this->getActivityConfig();
        $action = $config[$task['type']]['controller'].':finishCondition';

        return $this->forward($action, array('activity' => $activity));
    }

    /**
     * 没有权限进行任务的时候的处理逻辑，目前只有学员动态跳转过来的时候跳转到教学计划营销页.
     *
     * @param \Exception $exception
     * @param Request    $request
     * @param  $taskId
     *
     * @throws \Exception
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function handleAccessDeniedException(\Exception $exception, Request $request, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
        $courseSet = $this->getCourseSetService()->getCourseSet($task['fromCourseSetId']);

        $message = "您还不是课程《{$courseSet['title']}》的学员，请先购买或加入学习。";
        if ('the Task is Locked' == $exception->getMessage()) {
            $message = '先解锁上一任务才能继续学习';
        }

        return $this->createMessageResponse(
            'info',
            $message,
            '提示消息',
            3,
            $this->generateUrl(
                'course_show',
                array(
                    'id' => $task['courseId'],
                )
            )
        );
    }

    protected function tryLearnTask($courseId, $taskId, $preview = false)
    {
        if ($preview) {
            if ($this->getCourseService()->hasCourseManagerRole($courseId)) {
                $task = $this->getTaskService()->getTask($taskId);
            } else {
                $this->createNewException(TaskException::FORBIDDEN_PREVIEW_TASK());
            }
        } else {
            $isTeacher = $this->getCourseMemberService()->isCourseTeacher($courseId, $this->getUser()->getId());
            if ($isTeacher) {
                $task = $this->getTaskService()->getTask($taskId);
            } else {
                $task = $this->getTaskService()->tryTakeTask($taskId);
            }
        }
        if (empty($task)) {
            $this->createNewException(TaskException::NOTFOUND_TASK());
        }

        if ($task['courseId'] != $courseId) {
            $this->createNewException(TaskException::ACCESS_DENIED());
        }

        return $task;
    }

    private function freshTaskLearnStat(Request $request, $taskId)
    {
        $key = 'task.'.$taskId;
        $session = $request->getSession();
        $taskStore = $session->get($key, array());
        $taskStore['start'] = time();
        $taskStore['lastTriggerTime'] = 0;

        $session->set($key, $taskStore);
    }

    private function validTaskLearnStat(Request $request, $taskId)
    {
        $key = 'task.'.$taskId;
        $session = $request->getSession();
        $taskStore = $session->get($key);

        if (!empty($taskStore)) {
            $now = time();
            //任务连续学习超过5小时则不再统计时长
            if ($now - $taskStore['start'] > 60 * 60 * 5) {
                return false;
            }
            //任务每分钟只允许触发一次，这里用设定周期-5秒作为标准判断，以应对网络延迟
            $learnTimeSec = $this->getTaskService()->getTimeSec('learn');
            if ($now - $taskStore['lastTriggerTime'] < $learnTimeSec - 5) {
                return false;
            }
            $taskStore['lastTriggerTime'] = $now;
            $session->set($key, $taskStore);

            return true;
        }

        return false;
    }

    protected function isCourseExpired($course)
    {
        return (
                'date' == $course['expiryMode']
                && ($course['expiryStartDate'] > time() || $course['expiryEndDate'] < time())
            )
            || (
                'endDate' == $course['expiryMode'] && $course['expiryEndDate'] < time()
            );
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return TaskResultService
     */
    protected function getTaskResultService()
    {
        return $this->createService('Task:TaskResultService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->createService('Course:MemberService');
    }

    /**
     * @return TokenService
     */
    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return LearningDataAnalysisService
     */
    protected function getLearningDataAnalysisService()
    {
        return $this->createService('Course:LearningDataAnalysisService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getActivityConfig()
    {
        return $this->get('extension.manager')->getActivities();
    }
}
