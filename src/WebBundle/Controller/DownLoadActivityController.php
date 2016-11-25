<?php
/**
 * User: Edusoho V8
 * Date: 03/11/2016
 * Time: 10:05
 */

namespace WebBundle\Controller;


use Biz\DownloadActivity\Service\DownloadActivityService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\Course\CourseService;

class DownLoadActivityController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity             = $this->getActivityService()->getActivity($id);
        $activity['courseId'] = $courseId;
        return $this->render('WebBundle:DownLoadActivity:show.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity  = $this->getActivityService()->getActivity($id);
        $materials = array();

        foreach ($activity['ext']['materials'] as $media) {
            $id             = empty($media['fileId']) ? $media['link'] : $media['fileId'];
            $materials[$id] = array('id' => $media['fileId'], 'size' => $media['fileSize'], 'name' => $media['title'], 'link' => $media['link']);
        }
        $activity['ext']['materials'] = $materials;
        return $this->render('WebBundle:DownLoadActivity:modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId
        ));
    }

    public function downloadFileAction(Request $request, $courseId, $activityId)
    {

        $this->getCourseService()->tryLearnCourse($courseId);
        $mediaId  = $request->query->get('fileId');
        $activity = $this->getActivityService()->getActivity($activityId);

        $medias = empty($activity['ext']['materials']) ? array() : $activity['ext']['materials'];
        if (empty($medias)) {
            return $this->createNotFoundException('activity not found');
        }
        $medias = ArrayToolkit::index($medias, 'id');
        if (empty($medias[$mediaId])) {
            return $this->createNotFoundException('file not found');
        }

        $response = null;
        if (!empty($medias[$mediaId]['link'])) {
            $response = $this->redirect($medias[$mediaId]['link']);
        } else {
            $response = $this->forward("MaterialLibBundle:MaterialLib:download", array('fileId' => $medias[$mediaId]['fileId']));
        }
        $this->getDownloadActivityService()->createDownloadFileRecord($medias[$mediaId]);
        return $response;

    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('WebBundle:DownLoadActivity:modal.html.twig', array(
            'courseId' => $courseId
        ));
    }

    protected function getActivityService()
    {
        return $this->getBiz()->service('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {

        return ServiceKernel::instance()->createService('Course.CourseService');
    }

    /**
     * @return DownloadActivityService
     */
    protected function getDownloadActivityService()
    {
        return $this->getBiz()->service('DownloadActivity:DownloadActivityService');
    }
}