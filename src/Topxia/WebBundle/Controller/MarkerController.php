<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class MarkerController extends BaseController
{
    public function manageAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);
        $lesson = $this->getCourseService()->getCourseLesson($courseId, $lessonId);

        return $this->render('TopxiaWebBundle:Marker:index.html.twig', array(
            'course' => $course,
            'lesson' => $lesson
        ));
    }

    //驻点合并
    public function mergeAction(Request $request, $courseId, $lessonId)
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
        $file        = $this->getUploadFileService()->getFile($mediaId);

        foreach ($markersMeta as $key => $value) {
            foreach ($markersMeta[$key]['questionMarkers'] as $index => $questionMarker) {
                $markersMeta[$key]['questionMarkers'][$index]['includeImg'] = (preg_match('/<img (.*?)>/', $questionMarker['stem'])) ? true : false;

                if ($questionMarker['type'] == 'fill') {
                    $markersMeta[$key]['questionMarkers'][$index]['stem'] = preg_replace('/\[\[.+?\]\]/', '____', $questionMarker['stem']);
                }
            }
        }

        $result = array(
            'markersMeta' => $markersMeta,
            'videoTime'   => $file['length']
        );
        return $this->createJsonResponse($result);
    }

    //更新驻点时间
    public function updateMarkerAction(Request $request)
    {
        if (!$this->tryManageMarker()) {
            return $this->createJsonResponse(false);
        }

        $data       = $request->request->all();
        $data['id'] = isset($data['id']) ? $data['id'] : 0;
        $fields     = array(
            'updatedTime' => time(),
            'second'      => isset($data['second']) ? $data['second'] : ""
        );
        $marker = $this->getMarkerService()->updateMarker($data['id'], $fields);
        return $this->createJsonResponse($marker);
    }

    //获取当前播放器的驻点
    public function showMarkersAction(Request $request, $lessonId)
    {
        $data         = $request->request->all();
        $lesson       = $this->getCourseService()->getLesson($lessonId);
        $storage      = $this->getSettingService()->get('storage');
        $video_header = $this->getUploadFileService()->getFileByTargetType('headLeader');
        $markers      = $this->getMarkerService()->findMarkersByMediaId($lesson['mediaId']);
        $results      = array();
        $user         = $this->getUserService()->getCurrentUser();

        if ($this->agentInWhiteList($request->headers->get("user-agent")) ? 1 : 0) {
            return $this->createJsonResponse(array());
        }

        foreach ($markers as $key => $marker) {
            $results[$key]                    = $marker;
            $results[$key]['finish']          = $this->getMarkerService()->isFinishMarker($user['id'], $marker['id']);
            $results[$key]['videoHeaderTime'] = $storage['video_header'] ? intval($video_header['length']) : 0;
        }

        return $this->createJsonResponse($results);
    }

    protected function tryManageMarker()
    {
        $user = $this->getUserService()->getCurrentUser();

        if ($this->getUserService()->hasAdminRoles($user['id'])) {
            return true;
        }

        if (in_array("ROLE_TEACHER", $user['roles'])) {
            return true;
        }

        return false;
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getMarkerService()
    {
        return $this->getServiceKernel()->createService('Marker.MarkerService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
