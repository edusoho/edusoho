<?php
namespace AppBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\LiveReplayService;
use Biz\File\Service\UploadFileService;
use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\BaseStrategy;
use Biz\Task\Strategy\CourseStrategy;
use Biz\Task\Strategy\StrategyContext;
use Biz\Util\EdusohoLiveClient;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Exception\InvalidArgumentException;

class TaskManageController extends BaseController
{
    public function createAction(Request $request, $courseId)
    {
        $course     = $this->tryManageCourse($courseId);
        $categoryId = $request->query->get('categoryId');
        $chapterId  = $request->query->get('chapterId');
        $taskMode   = $request->query->get('type');
        if ($request->isMethod('POST')) {
            $task                    = $request->request->all();
            $task['_base_url']       = $request->getSchemeAndHttpHost();
            $task['fromUserId']      = $this->getUser()->getId();
            $task['fromCourseSetId'] = $course['courseSetId'];

            $task = $this->getTaskService()->createTask($this->parseTimeFields($task));

            if ($course['isDefault'] && isset($task['mode']) && $task['mode'] != 'lesson') {
                return $this->createJsonResponse(array('append' => false));
            }

            return $this->render($this->getTaskItemTemplate($course), array(
                'course' => $course,
                'task'   => $task
            ));
        }
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        return $this->render('task-manage/modal.html.twig', array(
            'mode'       => 'create',
            'course'     => $course,
            'courseSet'  => $courseSet,
            'categoryId' => $categoryId,
            'chapterId'  => $chapterId,
            'taskMode'   => $taskMode,
        ));
    }

    protected function getTaskItemTemplate($course)
    {
        if ($course['isDefault']) {
            return 'task-manage/list-item.html.twig';
        } else {
            return 'task-manage/list-item-lock-mode.html.twig';
        }
    }

    public function updateAction(Request $request, $courseId, $id)
    {
        $course   = $this->tryManageCourse($courseId);
        $task     = $this->getTaskService()->getTask($id);
        $taskMode = $request->query->get('type');
        if ($task['courseId'] != $courseId) {
            throw new InvalidArgumentException('任务不在计划中');
        }

        if ($request->getMethod() == 'POST') {
            $task              = $request->request->all();
            $task['_base_url'] = $request->getSchemeAndHttpHost();
            $this->getTaskService()->updateTask($id, $this->parseTimeFields($task));
            return $this->createJsonResponse(array('append' => false));
        }

        $activity  = $this->getActivityService()->getActivity($task['activityId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        return $this->render('task-manage/modal.html.twig', array(
            'mode'        => 'edit',
            'currentType' => $activity['mediaType'],
            'course'      => $course,
            'courseSet'   => $courseSet,
            'task'        => $task,
            'taskMode'    => $taskMode,
        ));
    }

    public function publishAction(Request $request, $courseId, $id)
    {
        $this->tryManageCourse($courseId);
        $this->getTaskService()->publishTask($id);

        return $this->createJsonResponse(array('success' => true));
    }

    public function unPublishAction(Request $request, $courseId, $id)
    {
        $this->tryManageCourse($courseId);
        $this->getTaskService()->unpublishTask($id);

        return $this->createJsonResponse(array('success' => true));
    }

    public function taskFieldsAction(Request $request, $courseId, $mode)
    {
        $course = $this->tryManageCourse($courseId);

        if ($mode === 'create') {
            $type = $request->query->get('type');
            return $this->forward('AppBundle:Activity/Activity:create', array(
                'courseId' => $courseId,
                'type'     => $type
            ));
        } else {
            $id   = $request->query->get('id');
            $task = $this->getTaskService()->getTask($id);
            return $this->forward('AppBundle:Activity/Activity:update', array(
                'id'       => $task['activityId'],
                'courseId' => $courseId
            ));
        }
    }

    public function deleteAction(Request $request, $courseId, $taskId)
    {
        $course = $this->tryManageCourse($courseId);
        $task   = $this->getTaskService()->getTask($taskId);
        if ($task['courseId'] != $courseId) {
            throw new InvalidArgumentException('任务不在课程中');
        }

        $this->getTaskService()->deleteTask($taskId);
        return $this->createJsonResponse(array('success' => true));
    }

    public function editTaskReplayAction(Request $request, $courseId, $taskId)
    {
        $course  = $this->getCourseService()->tryManageCourse($courseId);
        $task    = $this->getTaskService()->getTask($taskId);
        $replays = $this->getLiveReplayService()->findReplayByLessonId($task['id']);

        if ($request->getMethod() == 'POST') {
            $ids = $request->request->get("visibleReplaies");
            $this->getLiveReplayService()->updateReplayShow($ids, $taskId);
            return $this->redirect($this->generateUrl('course_set_manage_course_replay', array(
                'courseSetId' => $course['courseSetId'],
                'courseId'    => $course['id']
            )));
        }

        return $this->render('course-manage/live-replay/modal.html.twig', array(
            'replays' => $replays,
            'taskId'  => $task['id'],
            'course'  => $course,
            'task'    => $task
        ));
    }

    public function updateTaskReplayTitleAction(Request $request, $courseId, $taskId, $replayId)
    {
        $title = $request->request->get('title');

        if (empty($title)) {
            return $this->createJsonResponse(false);
        }

        $this->getLiveReplayService()->updateReplay($replayId, array('title' => $title));
        return $this->createJsonResponse(true);
    }

    public function uploadReplayAction(Request $request, $courseId, $taskId)
    {
        $course   = $this->getCourseService()->tryManageCourse($courseId);
        $task     = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        if ($activity['ext']['replayStatus'] == 'videoGenerated') {
            $file = $this->getUploadFileService()->getFile($activity['ext']['mediaId']);
            if (!empty($file)) {
                $task['media'] = array(
                    'id'     => $file['id'],
                    'status' => $file['convertStatus'],
                    'source' => 'self',
                    'name'   => $file['filename'],
                    'uri'    => ''
                );
            } else {
                $task['media'] = array('id' => 0, 'status' => 'none', 'source' => '', 'name' => '文件已删除', 'uri' => '');
            }
        }

        if ($request->getMethod() == 'POST') {
            $liveId        = $activity['ext']['liveId'];
            $liveProvideId = $activity['ext']['liveProvider'];
            $this->getLiveReplayService()->generateReplay($liveId, $courseId, $taskId, $liveProvideId, 'live');
        }

        return $this->render('course-manage/live-replay/upload-modal.html.twig', array(
            'course'   => $course,
            'task'     => $task,
            'activity' => $activity
        ));
    }

    public function createReplayAction(Request $request, $courseId, $taskId)
    {
        $course   = $this->getCourseService()->tryManageCourse($courseId);
        $task     = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId'], true);

        $liveId     = $activity['ext']['liveId'];
        $provider   = $activity['ext']['liveProvider'];
        $resultList = $this->getLiveReplayService()->generateReplay($liveId, $course['id'], $task['id'], $provider, 'live');

        if (array_key_exists("error", $resultList)) {
            return $this->createJsonResponse($resultList, 500);
        }

        $task["isEnd"]     = intval(time() - $task["endTime"]) > 0;
        $task["canRecord"] = $this->get('web.twig.live_extension')->canRecord($liveId);

        $client = new EdusohoLiveClient();

        if ($task['type'] == 'live') {
            $result = $client->getMaxOnline($liveId);
            $this->getTaskService()->setTaskMaxOnlineNum($task['id'], $result['onLineNum']);
        }

        return $this->createJsonResponse(true);
    }

    protected function tryManageCourse($courseId)
    {
        return $this->getCourseService()->tryManageCourse($courseId);
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getActivityConfig()
    {
        return $this->get('extension.default')->getActivities();
    }

    /**
     * @param  $type
     *
     * @return mixed
     */
    protected function getActivityActionConfig($type)
    {
        $config = $this->getActivityConfig();
        return $config[$type]['actions'];
    }

    /**
     * @param $course
     *
     * @return BaseStrategy|CourseStrategy
     */
    protected function createCourseStrategy($course)
    {
        return StrategyContext::getInstance()->createStrategy($course['isDefault'], $this->get('biz'));
    }

    protected function parseTimeFields($fields)
    {
        if (!empty($fields['startTime'])) {
            $fields['startTime'] = strtotime($fields['startTime']);
        }
        if (!empty($fields['endTime'])) {
            $fields['endTime'] = strtotime($fields['endTime']);
        }

        return $fields;
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return LiveReplayService
     */
    protected function getLiveReplayService()
    {
        return $this->createService('Course:LiveReplayService');
    }
}
