<?php

namespace AppBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\File\Service\UploadFileService;
use Biz\Task\Service\TaskService;
use Biz\Task\Strategy\CourseStrategy;
use AppBundle\Util\UploaderToken;
use Biz\Task\TaskException;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\Exception\InvalidArgumentException;

class TaskManageController extends BaseController
{
    public function preCreateCheckAction(Request $request, $courseId)
    {
        $task = $request->request->all();
        $task['fromCourseId'] = $courseId;
        $this->getTaskService()->preCreateTaskCheck($this->parseTimeFields($task));

        return $this->createJsonResponse(array('success' => 1));
    }

    public function preUpdateCheckAction(Request $request, $courseId, $activityId)
    {
        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($courseId, $activityId);
        $taskId = $task['id'];

        $task = $request->request->all();
        $task['fromCourseId'] = $courseId;
        $this->getTaskService()->preUpdateTaskCheck($taskId, $this->parseTimeFields($task));

        return $this->createJsonResponse(array('success' => 1));
    }

    public function createAction(Request $request, $courseId)
    {
        $course = $this->tryManageCourse($courseId);

        $categoryId = $request->query->get('categoryId', 0);

        $taskCount = $this->getTaskService()->countTasks(array('courseId' => $course['id'], 'categoryId' => $categoryId));
        if ($taskCount >= 6) {
            return $this->createNewException(TaskException::TASK_NUM_LIMIT());
        }

        //categoryId  所属课时
        $taskMode = $request->query->get('type');
        if ($request->isMethod('POST')) {
            $task = $request->request->all();

            return $this->createTask($request, $task, $course);
        }
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        $html = $this->renderView(
            'task-manage/modal.html.twig',
            array(
                'mode' => 'create',
                'course' => $course,
                'courseSet' => $courseSet,
                'categoryId' => $categoryId,
                'taskMode' => $taskMode,
            )
        );

        return $this->createJsonResponse(array('code' => true, 'message', 'html' => $html));
    }

    public function batchCreateTasksAction(Request $request, $courseId)
    {
        $this->getCourseService()->tryManageCourse($courseId);
        $mode = $request->query->get('mode');
        if ($request->isMethod('POST')) {
            $fileId = $request->request->get('fileId');
            $file = $this->getUploadFileService()->getFile($fileId);

            if (!in_array($file['type'], array('document', 'video', 'audio', 'ppt', 'flash'))) {
                return $this->createJsonResponse(array('error' => '不支持的文件类型'));
            }

            $course = $this->getCourseService()->getCourse($courseId);
            $task = $this->createTaskByFileAndCourse($file, $course);
            $task['mode'] = $mode;

            return $this->createTask($request, $task, $course);
        }

        $token = $request->query->get('token');
        $parser = new UploaderToken();
        $params = $parser->parse($token);

        if (!$params) {
            return $this->createJsonResponse(array('error' => 'bad token'));
        }

        return $this->render(
            'course-manage/batch-create/batch-create-modal.html.twig',
            array(
                'token' => $token,
                'targetType' => $params['targetType'],
                'courseId' => $courseId,
                'mode' => $mode,
            )
        );
    }

    private function createTaskByFileAndCourse($file, $course)
    {
        $task = array(
            'mediaType' => $file['type'],
            'fromCourseId' => $course['id'],
            'courseSetType' => 'normal',
            'media' => json_encode(array('source' => 'self', 'id' => $file['id'], 'name' => $file['filename'])),
            'mediaId' => $file['id'],
            'type' => $file['type'],
            'length' => $file['length'],
            'title' => str_replace(strrchr($file['filename'], '.'), '', $file['filename']),
            'ext' => array('mediaSource' => 'self', 'mediaId' => $file['id']),
            'categoryId' => 0,
        );
        if ('document' == $file['type']) {
            $task['type'] = 'doc';
            $task['mediaType'] = 'doc';
        }

        return $task;
    }

    /**
     * @param Request $request
     * @param         $task
     * @param         $course
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    private function createTask(Request $request, $task, $course)
    {
        $task['_base_url'] = $request->getSchemeAndHttpHost();
        $task['fromUserId'] = $this->getUser()->getId();
        $task['fromCourseSetId'] = $course['courseSetId'];

        $defaultFinishCondition = $this->getDefaultFinishCondition($task['mediaType']);
        $task = array_merge($defaultFinishCondition, $task);
        $task = $this->getTaskService()->createTask($this->parseTimeFields($task));

        return $this->getTaskJsonView($task);
    }

    public function updateAction(Request $request, $courseId, $id)
    {
        $course = $this->tryManageCourse($courseId);
        $task = $this->getTaskService()->getTask($id);
        $taskMode = $request->query->get('type');
        $customTitle = $request->query->get('customTitle', '');
        if ($task['courseId'] != $courseId) {
            throw new InvalidArgumentException('任务不在计划中');
        }

        if ('POST' == $request->getMethod()) {
            $task = $request->request->all();

            if (!isset($task['isOptional'])) {
                $task['isOptional'] = 0;
            }

            if (!isset($task['fromCourseSetId'])) {
                $task['fromCourseSetId'] = $course['courseSetId'];
            }

            $task = $this->getTaskService()->updateTask($id, $this->parseTimeFields($task));

            return $this->getTaskJsonView($task);
        }

        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);

        return $this->render(
            'task-manage/modal.html.twig',
            array(
                'mode' => 'edit',
                'customTitle' => $customTitle,
                'currentType' => $activity['mediaType'],
                'course' => $course,
                'courseSet' => $courseSet,
                'task' => $task,
                'taskMode' => $taskMode,
            )
        );
    }

    //创建任务或修改任务返回的html
    private function getTaskJsonView($task)
    {
        $course = $this->getCourseService()->getCourse($task['courseId']);
        $taskJsonData = $this->createCourseStrategy($course)->getTasksJsonData($task);
        if (empty($taskJsonData)) {
            return $this->createJsonResponse(false);
        }

        return $this->createJsonResponse($this->renderView(
            $taskJsonData['template'],
            $taskJsonData['data']
        ));
    }

    public function publishAction(Request $request, $courseId, $id)
    {
        $this->tryManageCourse($courseId);
        $task = $this->getTaskService()->publishTask($id);
        if (false === $task) {
            return $this->createJsonResponse(array('success' => false, 'message' => $this->trans('course.task.classroom_sync_job_executing_tips')));
        }

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

        if ('create' === $mode) {
            $type = $request->query->get('type');

            return $this->forward(
                'AppBundle:Activity/Activity:create',
                array(
                    'courseId' => $courseId,
                    'type' => $type,
                )
            );
        } else {
            $id = $request->query->get('id');
            $task = $this->getTaskService()->getTask($id);
            $this->getCourseService()->tryManageCourse($task['courseId']);

            return $this->forward(
                'AppBundle:Activity/Activity:update',
                array(
                    'id' => $task['activityId'],
                    'courseId' => $courseId,
                )
            );
        }
    }

    public function deleteAction(Request $request, $courseId, $taskId)
    {
        $task = $this->getTaskService()->getTask($taskId);
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
        return $this->get('extension.manager')->getActivities();
    }

    /**
     * @param $course
     *
     * @return CourseStrategy
     */
    protected function createCourseStrategy($course)
    {
        return $this->getBiz()->offsetGet('course.strategy_context')->createStrategy($course['courseType']);
    }

    private function getDefaultFinishCondition($mediaType)
    {
        $activityConfigManager = $this->get('activity_config_manager');
        $activityConfig = $activityConfigManager->getInstalledActivity($mediaType);
        if (empty($activityConfig['finish_condition'])) {
            return array();
        }
        $findishCondition = reset($activityConfig['finish_condition']);

        return array(
            'finishType' => $findishCondition['type'],
            'finishData' => empty($findishCondition['value']) ? '' : $findishCondition['value'],
        );
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

    protected function getChapterDao()
    {
        return $this->getBiz()->dao('Course:CourseChapterDao');
    }
}
