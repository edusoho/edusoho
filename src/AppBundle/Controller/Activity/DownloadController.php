<?php

namespace AppBundle\Controller\Activity;


use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MaterialService;
use Symfony\Component\HttpFoundation\Request;

class DownloadController extends BaseController implements ActivityActionInterface
{
    public function showAction(Request $request, $id, $courseId)
    {
        $activity             = $this->getActivityService()->getActivity($id, $fetchMedia = true);
        $activity['courseId'] = $courseId;
        $materials            = $this->getMaterialService()->findMaterialsByLessonIdAndSource($activity['id'], 'coursematerial');
        return $this->render('activity/download/show.html.twig', array(
            'materials' => $materials,
            'activity'  => $activity,
            'courseId'  => $courseId
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity  = $this->getActivityService()->getActivity($id, $fetchMedia = true);
        $materials = $this->getMaterialService()->findMaterialsByLessonIdAndSource($activity['id'], 'coursematerial');
        foreach ($materials as $material) {
            $id                                = empty($material['fileId']) ? $material['link'] : $material['fileId'];
            $activity['ext']['materials'][$id] = array('id' => $material['fileId'], 'size' => $material['fileSize'], 'name' => $material['title'], 'link' => $material['link']);
        }
        return $this->render('activity/download/modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId
        ));
    }

    public function downloadFileAction(Request $request, $courseId, $activityId)
    {

        $this->getCourseService()->tryTakeCourse($courseId);

        $downloadFileId = $request->query->get('fileId');
        $downloadFile   = $this->getDownloadActivityService()->downloadActivityFile($activityId, $downloadFileId);
        if (!empty($downloadFile['link'])) {
            return $this->redirect($downloadFile['link']);
        } else {
            return $this->forward('AppBundle:UploadFile:download', array(
                'request' => $request,
                'fileId'  => $downloadFile['fileId']
            ));
        }
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/download/modal.html.twig', array(
            'courseId' => $courseId
        ));
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {

        return $this->createService('Course:CourseService');
    }

    /**
     * @return DownloadActivityService
     */
    protected function getDownloadActivityService()
    {
        return $this->createService('Activity:DownloadActivityService');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }
}