<?php

namespace AppBundle\Controller\Activity;

use AppBundle\Util\PlayToken;
use Biz\Activity\ActivityException;
use Biz\Activity\DownloadActivityException;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\DownloadActivityService;
use Biz\Course\MaterialException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\MaterialService;
use Biz\File\Service\UploadFileService;
use Symfony\Component\HttpFoundation\Request;

class DownloadController extends BaseActivityController implements ActivityActionInterface
{
    public function showAction(Request $request, $activity)
    {
        $download = $this->getActivityService()->getActivityConfig($activity['mediaType'])->get($activity['mediaId']);
        $materials = $this->getMaterialService()->findMaterialsByLessonIdAndSource($activity['id'], 'coursematerial');

        return $this->render('activity/download/show.html.twig', [
            'materials' => $materials,
            'activity' => $activity,
            'download' => $download,
        ]);
    }

    public function editAction(Request $request, $id, $courseId)
    {
        $activity = $this->getActivityService()->getActivity($id, $fetchMedia = true);
        $materials = $this->getMaterialService()->findMaterialsByLessonIdAndSource($activity['id'], 'coursematerial');

        foreach ($materials as $material) {
            $id = empty($material['fileId']) ? $material['link'] : $material['fileId'];
            $activity['ext']['materials'][$id] = ['id' => $material['fileId'], 'size' => $material['fileSize'], 'name' => $material['title'], 'link' => $material['link']];
        }

        return $this->render('activity/download/modal.html.twig', [
            'activity' => $activity,
            'courseId' => $courseId,
        ]);
    }

    public function downloadFileAction(Request $request, $courseId, $activityId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);

        $materialId = $request->query->get('materialId');
        $downloadFile = $this->getDownloadActivityService()->downloadActivityFile($courseId, $activityId, $materialId);

        if (!empty($downloadFile['link'])) {
            return $this->redirect($downloadFile['link']);
        } else {
            return $this->forward('AppBundle:UploadFile:download', [
                'request' => $request,
                'fileId' => $downloadFile['fileId'],
            ]);
        }
    }

    public function previewFileAction(Request $request, $courseId, $activityId, $materialId)
    {
        $this->getCourseService()->tryTakeCourse($courseId);
        $activity = $this->getActivityService()->getActivity($activityId, true);
        if (empty($activity)) {
            $this->createNewException(ActivityException::NOTFOUND_ACTIVITY());
        }
        if ($courseId != $activity['fromCourseId']) {
            $this->createNewException(ActivityException::ACTIVITY_NOT_IN_COURSE());
        }
        $material = $this->getMaterialService()->getMaterial($courseId, $materialId);
        if (empty($material)) {
            $this->createNewException(MaterialException::NOTFOUND_MATERIAL());
        }
        if (!in_array($material['fileId'], $activity['ext']['fileIds'])) {
            $this->createNewException(DownloadActivityException::FILE_NOT_IN_ACTIVITY());
        }

        return $this->render('material-lib/web/preview.html.twig', [
            'file' => $this->getUploadFileService()->getFullFile($material['fileId']),
            'type' => 'modal',
            'token' => (new PlayToken())->make($material['fileId']),
        ]);
    }

    public function createAction(Request $request, $courseId)
    {
        return $this->render('activity/download/modal.html.twig', [
            'courseId' => $courseId,
        ]);
    }

    public function previewAction(Request $request, $task)
    {
        return $this->render('activity/no-preview.html.twig');
    }

    public function finishConditionAction(Request $request, $activity)
    {
        return $this->render('activity/download/finish-condition.html.twig', []);
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

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }
}
