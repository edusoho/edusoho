<?php
namespace WebBundle\Controller;

use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\StrategyContext;
use Topxia\Service\Course\CourseService;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Exception\InvalidArgumentException;

class TaskManageController extends BaseController
{
    public function createAction(Request $request, $courseId)
    {
        $course     = $this->tryManageCourse($courseId);
        $taskMode   = $request->query->get('type');
        $categoryId = $request->query->get('categoryId');
        $chapterId  = $request->query->get('chapterId');
        if ($request->isMethod('POST')) {
            $task               = $request->request->all();
            $task['_base_url']  = $request->getSchemeAndHttpHost();
            $task['fromUserId'] = $this->getUser()->getId();
            $task               = $this->getTaskService()->createTask($this->parseTimeFields($task));

            $tasksRenderPage = $this->createCourseStrategy($course)->getTaskItemRenderPage();
            return $this->render($tasksRenderPage, array(
                'course' => $course,
                'task'   => $task
            ));

        }

        return $this->render('WebBundle:TaskManage:modal.html.twig', array(
            'course'     => $course,
            'mode'       => 'create',
            'types'      => $this->getActivityService()->getActivityTypes(),
            'taskMode'   => $taskMode,
            'categoryId' => $categoryId,
            'chapterId'  => $chapterId
        ));
    }

    public function updateAction(Request $request, $courseId, $id)
    {
        $course   = $this->tryManageCourse($courseId);
        $task     = $this->getTaskService()->getTask($id);
        $taskMode = $request->query->get('type');
        if ($task['courseId'] != $courseId) {
            throw new InvalidArgumentException('任务不在课程中');
        }

        if ($request->getMethod() == 'POST') {
            $task               = $request->request->all();
            $task['_base_url']  = $request->getSchemeAndHttpHost();
            $task['fromUserId'] = $this->getUser()->getId();
            $savedTask          = $this->getTaskService()->updateTask($id, $this->parseTimeFields($task));
            return $this->createJsonResponse(true);
        }

        $activity       = $this->getActivityService()->getActivity($task['activityId']);
        $config         = $this->getActivityService()->getActivityConfig($activity['mediaType']);
        $editController = $config->getAction('edit');

        return $this->render('WebBundle:TaskManage:modal.html.twig', array(
            'mode'                => 'edit',
            'currentType'         => $activity['mediaType'],
            'activity'            => $activity,
            'types'               => $this->getActivityService()->getActivityTypes(),
            'activity_controller' => $editController,
            'course'              => $course,
            'task'                => $task,
            'taskMode'            => $taskMode
        ));
    }

    public function publishAction(Request $request, $courseId, $id)
    {
        $this->tryManageCourse($courseId, $id);
        $this->getTaskService()->publishTask($id);

        return $this->createJsonResponse(array('success' => true));
    }

    public function unPublishAction(Request $request, $courseId, $id)
    {
        $this->tryManageCourse($courseId, $id);
        $this->getTaskService()->unPublishTask($id);

        return $this->createJsonResponse(array('success' => true));
    }

    public function taskFieldsAction(Request $request, $courseId, $mode)
    {
        $course = $this->tryManageCourse($courseId);

        if ($mode === 'create') {
            $type = $request->query->get('type');
            return $this->forward('WebBundle:Activity:create', array(
                'courseId' => $courseId,
                'type'     => $type
            ));
        } else {
            $id   = $request->query->get('id');
            $task = $this->getTaskService()->getTask($id);
            return $this->forward('WebBundle:Activity:update', array(
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
