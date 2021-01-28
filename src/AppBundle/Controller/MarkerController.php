<?php

namespace AppBundle\Controller;

use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Marker\Service\MarkerService;
use Biz\Task\Service\TaskService;
use Biz\User\Service\UserService;
use Symfony\Component\HttpFoundation\Request;

class MarkerController extends BaseController
{
    public function manageAction(Request $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $task = $this->getTaskService()->getCourseTask($courseId, $taskId);
        $activity = [];
        if (!empty($task)) {
            $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        }

        return $this->render('marker/index.html.twig', [
            'course' => $course,
            'task' => $task,
            'activity' => $activity,
        ]);
    }

    public function previewAction(Request $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $task = $this->getTaskService()->getCourseTask($courseId, $taskId);

        $activity = [];
        if (!empty($task)) {
            $activity = $this->getActivityService()->getActivity($task['activityId'], true);
        }

        return $this->render('marker/preview.html.twig', [
            'course' => $course,
            'task' => $task,
            'activity' => $activity,
        ]);
    }

    //驻点合并
    public function mergeAction(Request $request, $courseId, $taskId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $data = $request->request->all();

        if (empty($data['sourceMarkerId']) || empty($data['targetMarkerId'])) {
            return $this->createMessageResponse('error', '参数错误!');
        }

        $this->getMarkerService()->merge($data['sourceMarkerId'], $data['targetMarkerId']);

        return $this->createJsonResponse(true);
    }

    public function markerMetasAction(Request $request, $mediaId)
    {
        if (!$this->tryManageMarker()) {
            return $this->createJsonResponse(false);
        }

        $markersMeta = $this->getMarkerService()->findMarkersMetaByMediaId($mediaId);
        $file = $this->getUploadFileService()->getFile($mediaId);

        foreach ($markersMeta as $key => $value) {
            foreach ($markersMeta[$key]['questionMarkers'] as $index => $questionMarker) {
                if ('fill' == $questionMarker['type']) {
                    $markersMeta[$key]['questionMarkers'][$index]['stem'] = preg_replace('/\[\[.+?\]\]/', '____', $questionMarker['stem']);
                }
            }
        }

        $result = [
            'markersMeta' => $markersMeta,
            'videoTime' => $file['length'],
        ];

        return $this->createJsonResponse($result);
    }

    //更新驻点时间
    public function updateMarkerAction(Request $request)
    {
        if (!$this->tryManageMarker()) {
            return $this->createJsonResponse(false);
        }

        $data = $request->request->all();
        $data['id'] = isset($data['id']) ? $data['id'] : 0;
        $fields = [
            'updatedTime' => time(),
            'second' => isset($data['second']) ? $data['second'] : '',
        ];
        $marker = $this->getMarkerService()->updateMarker($data['id'], $fields);

        return $this->createJsonResponse($marker);
    }

    //获取当前播放器的驻点
    public function showMarkersAction(Request $request, $taskId)
    {
        $data = $request->request->all();
        $task = $this->getTaskService()->getTask($taskId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $storage = $this->getSettingService()->get('storage');
        $video_header = $this->getUploadFileService()->getFileByTargetType('headLeader');
        $markers = $this->getMarkerService()->findMarkersByMediaId($activity['ext']['file']['id']);
        $results = [];
        $user = $this->getUserService()->getCurrentUser();

        if ($this->agentInWhiteList($request->headers->get('user-agent')) ? 1 : 0) {
            return $this->createJsonResponse([]);
        }

        foreach ($markers as $key => $marker) {
            $results[$key] = $marker;
            $results[$key]['finish'] = $this->getMarkerService()->isFinishMarker($user['id'], $marker['id']);
            $results[$key]['videoHeaderTime'] = $storage['video_header'] ? intval($video_header['length']) : 0;
        }

        return $this->createJsonResponse($results);
    }

    protected function tryManageMarker()
    {
        $user = $this->getCurrentUser();

        if ($this->getUserService()->hasAdminRoles($user['id'])) {
            return true;
        }

        if (in_array('ROLE_TEACHER', $user['roles'])) {
            return true;
        }

        return false;
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return MarkerService
     */
    protected function getMarkerService()
    {
        return $this->createService('Marker:MarkerService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
    }

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
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
}
