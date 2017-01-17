<?php
namespace AppBundle\Controller;

use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\BaseStrategy;
use Biz\Task\Strategy\StrategyContext;
use Biz\Activity\Service\ActivityService;
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

            $task                    = $this->getTaskService()->createTask($this->parseTimeFields($task));

            if ($course['isDefault'] && isset($task['mode']) && $task['mode'] != 'lesson') {
                return $this->createJsonResponse(array('append' => false));
            }

            $tasksRenderPage = $this->createCourseStrategy($course)->getTaskItemRenderPage();
            return $this->render($tasksRenderPage, array(
                'course' => $course,
                'task'   => $task
            ));
        }

        return $this->render('task-manage/modal.html.twig', array(
            'mode'       => $taskMode,
            'course'     => $course,
            'categoryId' => $categoryId,
            'chapterId'  => $chapterId
        ));
    }

    public function updateAction(Request $request, $courseId, $id)
    {
        $course   = $this->tryManageCourse($courseId);
        $task     = $this->getTaskService()->getTask($id);
        $taskMode   = $request->query->get('type');
        if ($task['courseId'] != $courseId) {
            throw new InvalidArgumentException('任务不在计划中');
        }

        if ($request->getMethod() == 'POST') {
            $task              = $request->request->all();
            $task['_base_url'] = $request->getSchemeAndHttpHost();
            $this->getTaskService()->updateTask($id, $this->parseTimeFields($task));
            return $this->createJsonResponse(array('append' => false));
        }

        $activity = $this->getActivityService()->getActivity($task['activityId']);

        return $this->render('task-manage/modal.html.twig', array(
            'mode'                => $taskMode,
            'currentType'         => $activity['mediaType'],
            'course'              => $course,
            'task'                => $task,
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
     * @return mixed
     */
    protected function getActivityActionConfig($type)
    {
        $config = $this->getActivityConfig();
        return $config[$type]['actions'];
    }

    protected function createCourseStrategy($course)
    {
        return StrategyContext::getInstance()->createStrategy($course['isDefault'], $this->get('biz'));
    }

    //datetime to int
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
}
