<?php

namespace AppBundle\Controller\Activity;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\MaterialService;
use Biz\Activity\Service\ActivityService;
use Symfony\Component\HttpFoundation\Request;
use Biz\Activity\Service\DownloadActivityService;

class DownloadController extends BaseActivityController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity)
    {
        $download = $this->getActivityService()->getActivityConfig($activity['mediaType'])->get($activity['mediaId']);
        $materials = $this->getMaterialService()->findMaterialsByLessonIdAndSource($activity['id'], 'coursematerial');

        return $this->render('activity/download/show.html.twig', array(
            'materials' => $materials,
            'activity' => $activity,
            'download' => $download,
        ));
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id, $fetchMedia = true);
        $materials = $this->getMaterialService()->findMaterialsByLessonIdAndSource($activity['id'], 'coursematerial');

        foreach ($materials as $material) {
            $id = empty($material['fileId']) ? $material['link'] : $material['fileId'];
            $activity['ext']['materials'][$id] = array('id' => $material['fileId'], 'size' => $material['fileSize'], 'name' => $material['title'], 'link' => $material['link']);
        }

        return $this->render('activity/download/modal.html.twig', array(
            'activity' => $activity,
            'courseId' => $courseId,
        ));
    }

    public function downloadFileAction(Request $request, $courseId, $activityId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $materialId = $request->query->get('materialId');
        $downloadFile = $this->getDownloadActivityService()->downloadActivityFile($courseId, $activityId, $materialId);

        if (!empty($downloadFile['link'])) {
            return $this->redirect($downloadFile['link']);
        } else {
            return $this->forward('AppBundle:UploadFile:download', array(
                'request' => $request,
                'fileId' => $downloadFile['fileId'],
            ));
        }
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/download/modal.html.twig', array(
            'courseId' => $courseId,
        ));
    }

    public function previewAction(Request $request, $task)
    {
        return $this->render('activity/no-preview.html.twig');
    }

    public function finishConditionAction(Request $request, $activity)
    {
        return $this->render('activity/download/finish-condition.html.twig', array());
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
