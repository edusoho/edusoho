<?php
namespace WebBundle\Controller;

use Biz\Task\Service\TaskService;
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
        if ($request->isMethod('POST')) {
            $task               = $request->request->all();
            $task['_base_url']  = $request->getSchemeAndHttpHost();
            $task['fromUserId'] = $this->getUser()->getId();
            $this->getTaskService()->createTask($this->parseTimeFields($task));
            return $this->createJsonResponse(true);
        }

        return $this->render('WebBundle:TaskManage:modal.html.twig', array(
            'course'     => $course,
            'mode'       => 'create',
            'types'      => $this->getActivityService()->getActivityTypes(),
            'taskMode'   => $taskMode,
            'categoryId' => $categoryId
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

    public function deleteAction(Request $request, $courseId, $id)
    {
        $course = $this->tryManageCourse($courseId);
        $task   = $this->getTaskService()->getTask($id);
        if ($task['courseId'] != $courseId) {
            throw new InvalidArgumentException('任务不在课程中');
        }

        $this->getTaskService()->deleteTask($id);
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

    //datetime to int
    protected function parseTimeFields($fields)
    {
        if (isset($fields['startTime']) && $fields['startTime'] != 0) {
            $fields['startTime'] = strtotime($fields['startTime']);
        }
        if (isset($fields['endTime']) && $fields['startTime'] != 0) {
            $fields['endTime'] = strtotime($fields['endTime']);
        }

        return $fields;
    }
}
