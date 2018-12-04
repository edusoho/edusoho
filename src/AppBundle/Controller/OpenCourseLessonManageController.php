<?php

namespace AppBundle\Controller;

use AppBundle\Common\Paginator;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\OpenCourse\OpenCourseException;
use Biz\System\Service\SettingService;
use Biz\File\Service\UploadFileService;
use Biz\CloudPlatform\Service\AppService;
use Biz\OpenCourse\Service\OpenCourseService;
use Symfony\Component\HttpFoundation\Request;

class OpenCourseLessonManageController extends BaseController
{
    public function lessonAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);
        $courseItems = $this->getOpenCourseService()->getLessonItems($course['id']);
        $mediaMap = array();

        foreach ($courseItems as $item) {
            if ($item['itemType'] != 'lesson') {
                continue;
            }

            if (empty($item['mediaId'])) {
                continue;
            }

            if (empty($mediaMap[$item['mediaId']])) {
                $mediaMap[$item['mediaId']] = array();
            }

            $mediaMap[$item['mediaId']][] = $item['id'];
        }

        $mediaIds = array_keys($mediaMap);
        $files = $this->getUploadFileService()->findFilesByIds($mediaIds);

        foreach ($files as $file) {
            $lessonIds = $mediaMap[$file['id']];

            foreach ($lessonIds as $lessonId) {
                $courseItems["lesson-{$lessonId}"]['mediaStatus'] = $file['convertStatus'];
            }
        }

        return $this->render('open-course-manage/lesson-list.html.twig', array(
            'course' => $course,
            'items' => $courseItems,
            'files' => ArrayToolkit::index($files, 'id'),
        ));
    }

    public function createAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);
        $parentId = $request->query->get('parentId');

        if ($this->lessonExists($id)) {
            return $this->createJsonResponse(array('result' => 'lessonExists'));
        }

        if ($request->getMethod() == 'POST') {
            $lesson = $request->request->all();
            $lesson['courseId'] = $course['id'];

            if (!empty($lesson['media'])) {
                $lesson['media'] = json_decode($lesson['media'], true);
            }

            if (is_numeric($lesson['second'])) {
                $lesson['length'] = $this->textToSeconds($lesson['minute'], $lesson['second']);
                unset($lesson['minute']);
                unset($lesson['second']);
            }

            $lesson = $this->getOpenCourseService()->createLesson($lesson);

            $file = false;

            if ($lesson['mediaId'] > 0 && ($lesson['type'] != 'testpaper')) {
                $file = $this->getUploadFileService()->getFile($lesson['mediaId']);

                if ($file['type'] == 'document' && $file['convertStatus'] == 'none') {
                    $convertHash = $this->getUploadFileService()->reconvertFile(
                        $file['id']
                    );
                }

                $lesson['mediaStatus'] = $file['convertStatus'];
            }

            $lessonId = 0;

            return $this->render('open-course-manage/lesson-list-item.html.twig', array(
                'course' => $course,
                'lesson' => $lesson,
                'file' => $file,
            ));
        }

        $user = $this->getCurrentUser();
        $randString = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 12);
        $filePath = "opencourselesson/{$course['id']}";
        $fileKey = "{$filePath}/".$randString;
        $convertKey = $randString;

        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();

        return $this->render('open-course-manage/lesson-modal.html.twig', array(
            'course' => $course,
            'courseId' => $course['id'],
            'targetType' => 'opencourselesson',
            'filePath' => $filePath,
            'fileKey' => $fileKey,
            'convertKey' => $convertKey,
            'storageSetting' => $this->setting('storage'),
            'features' => $features,
            'parentId' => $parentId,
            'courseType' => 'openCourse',
        ));
    }

    public function editAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($courseId);
        $lesson = $this->getOpenCourseService()->getCourseLesson($course['id'], $lessonId);

        if (empty($lesson)) {
            $this->createNewException(OpenCourseException::NOTFOUND_LESSON());
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            if ($fields['media']) {
                $fields['media'] = json_decode($fields['media'], true);
            }

            if ($fields['second']) {
                $fields['length'] = $this->textToSeconds($fields['minute'], $fields['second']);
                unset($fields['minute']);
                unset($fields['second']);
            }

            $lesson = $this->getOpenCourseService()->updateLesson($course['id'], $lesson['id'], $fields);

            $file = false;

            if ($lesson['mediaId'] > 0 && ($lesson['type'] != 'testpaper')) {
                $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
                $lesson['mediaStatus'] = $file['convertStatus'];

                if ($file['type'] == 'document' && $file['convertStatus'] == 'none') {
                    $convertHash = $this->getUploadFileService()->reconvertFile(
                        $file['id']
                    );
                }
            }

            return $this->render('open-course-manage/lesson-list-item.html.twig', array(
                'course' => $course,
                'lesson' => $lesson,
                'file' => $file,
            ));
        }

        $file = null;

        if ($lesson['mediaId']) {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);

            if (!empty($file)) {
                $lesson['media'] = array(
                    'id' => $file['id'],
                    'status' => $file['convertStatus'],
                    'source' => 'self',
                    'name' => $file['filename'],
                    'uri' => '',
                );
            } else {
                $lesson['media'] = array('id' => 0, 'status' => 'none', 'source' => '', 'name' => '文件已删除', 'uri' => '');
            }
        } else {
            $name = $this->hasSelfMedia($lesson) ? '文件已在课程文件中移除' : $lesson['mediaName'];
            $lesson['media'] = array(
                'id' => 0,
                'status' => 'none',
                'source' => $lesson['mediaSource'],
                'name' => $name,
                'uri' => $lesson['mediaUri'],
            );
        }

        list($lesson['minute'], $lesson['second']) = $this->secondsToText($lesson['length']);

        $user = $this->getCurrentUser();
        $randString = substr(base_convert(sha1(uniqid(mt_rand(), true)), 16, 36), 0, 12);
        $filePath = "opencourselesson/{$course['id']}";
        $fileKey = "{$filePath}/".$randString;
        $convertKey = $randString;

        $lesson['title'] = str_replace(array('"', "'"), array('&#34;', '&#39;'), $lesson['title']);

        $features = $this->container->hasParameter('enabled_features') ? $this->container->getParameter('enabled_features') : array();

        return $this->render('open-course-manage/lesson-modal.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
            'file' => $file,
            'targetType' => 'opencourselesson',
            'courseId' => $course['id'],
            'filePath' => $filePath,
            'fileKey' => $fileKey,
            'convertKey' => $convertKey,
            'storageSetting' => $this->setting('storage'),
            'features' => $features,
            'courseType' => 'openCourse',
        ));
    }

    public function publishAction(Request $request, $courseId, $lessonId)
    {
        $this->getOpenCourseService()->publishLesson($courseId, $lessonId);
        $course = $this->getOpenCourseService()->getCourse($courseId);
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);

        $file = false;

        if ($lesson['mediaId'] > 0 && ($lesson['type'] != 'testpaper')) {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
            $lesson['mediaStatus'] = $file['convertStatus'];
        }

        return $this->render('open-course-manage/lesson-list-item.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
            'file' => $file,
        ));
    }

    public function unpublishAction(Request $request, $courseId, $lessonId)
    {
        $this->getOpenCourseService()->unpublishLesson($courseId, $lessonId);

        $course = $this->getOpenCourseService()->getCourse($courseId);
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);
        $file = false;

        if ($lesson['mediaId'] > 0 && ($lesson['type'] != 'testpaper')) {
            $file = $this->getUploadFileService()->getFile($lesson['mediaId']);
            $lesson['mediaStatus'] = $file['convertStatus'];
        }

        return $this->render('open-course-manage/lesson-list-item.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
            'file' => $file,
        ));
    }

    public function deleteAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($courseId);
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);

        $this->getOpenCourseService()->deleteLesson($lessonId);

        return $this->createJsonResponse(true);
    }

    public function sortAction(Request $request, $id)
    {
        $ids = $request->request->get('ids');

        if (!empty($ids)) {
            $course = $this->getOpenCourseService()->tryManageOpenCourse($id);
            $this->getOpenCourseService()->sortCourseItems($course['id'], $request->request->get('ids'));
        }

        return $this->createJsonResponse(true);
    }

    public function materialModalAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($courseId);
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);

        $materials = $this->getMaterialService()->searchMaterials(
            array(
                'courseId' => $courseId,
                'lessonId' => $lesson['id'],
                'source' => 'opencoursematerial',
                'type' => 'openCourse',
            ),
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        return $this->render('open-course-manage/material-edit-modal.html.twig', array(
            'course' => $course,
            'lesson' => $lesson,
            'materials' => $materials,
            'storageSetting' => $this->setting('storage'),
            'targetType' => 'opencoursematerial',
            'courseType' => 'openCourse',
        ));
    }

    public function materialUploadAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($courseId);
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);

        if (empty($lesson)) {
            $this->createNewException(OpenCourseException::NOTFOUND_LESSON());
        }

        if ($request->getMethod() == 'POST') {
            $fields = $request->request->all();

            if (empty($fields['fileId']) && empty($fields['link'])) {
                $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
            }

            $fields['courseId'] = $course['id'];
            $fields['lessonId'] = $lessonId;
            $fields['type'] = 'openCourse';
            $fields['source'] = 'opencoursematerial';
            $fields['courseSetId'] = 0;

            $material = $this->getMaterialService()->uploadMaterial($fields);

            return $this->render('open-course-manage/material-list-item.html.twig', array(
                'material' => $material,
                'course' => $course,
            ));
        }
    }

    public function materialDeleteAction(Request $request, $courseId, $materialId)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($courseId);

        $material = $this->getMaterialService()->getMaterial($courseId, $materialId);
        if ($material) {
            $this->getMaterialService()->updateMaterial($materialId, array('lessonId' => 0), array('lessonId' => $material['lessonId'], 'materialId' => $materialId, 'fileId' => $material['fileId']));
        }

        return $this->createJsonResponse(true);
    }

    public function materialBrowserAction(Request $request, $courseId)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($courseId);

        $conditions = array();
        $type = $request->query->get('type');
        if (!empty($type)) {
            $conditions['type'] = $type;
        }

        $courseMaterialIds = $this->getMaterialService()->searchFileIds(
            array(
                'courseId' => $course['id'],
                'type' => 'openCourse',
            ),
            array('createdTime' => 'DESC'),
            0,
            PHP_INT_MAX
        );

        $conditions['ids'] = $courseMaterialIds ? $courseMaterialIds : array(-1);
        $paginator = new Paginator(
            $request,
            $this->getUploadFileService()->searchFileCount($conditions),
            20
        );

        $files = $this->getUploadFileService()->searchFiles(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->createFilesJsonResponse($files, $paginator);
    }

    public function draftCreateAction(Request $request)
    {
        $formData = $request->request->all();
        $user = $this->getCurrentUser();
        $userId = $user['id'];
        $courseId = $formData['courseId'];

        if (isset($formData['lessonId'])) {
            $lessonId = $formData['lessonId'];
        } else {
            $lessonId = 0;
            $formData['lessonId'] = 0;
        }

        $content = $formData['content'];

        $draft = $this->getCourseDraftService()->getCourseDraftByCourseIdAndActivityIdAndUserId($courseId, $lessonId, $userId);

        if ($draft) {
            $draft = $this->getCourseDraftService()->updateCourseDraft($draft['id'], $formData);
        } else {
            $draft = $this->getCourseDraftService()->createCourseDraft($formData);
        }

        return $this->createJsonResponse(true);
    }

    protected function textToSeconds($minutes, $seconds)
    {
        return intval($minutes) * 60 + intval($seconds);
    }

    protected function secondsToText($value)
    {
        $minutes = intval($value / 60);
        $seconds = $value - $minutes * 60;

        return array($minutes, $seconds);
    }

    protected function hasSelfMedia($lesson)
    {
        return !in_array($lesson['type'], array('liveOpen')) && $lesson['mediaSource'] == 'self';
    }

    protected function lessonExists($courseId)
    {
        $lessons = $this->getOpenCourseService()->searchLessons(array('courseId' => $courseId), array('seq' => 'ASC'), 0, 1);
        if ($lessons) {
            return true;
        }

        return false;
    }

    protected function createFilesJsonResponse($files, $paginator = null)
    {
        foreach ($files as &$file) {
            $file['updatedTime'] = $file['updatedTime'] ? $file['updatedTime'] : $file['createdTime'];
            $file['updatedTime'] = date('Y-m-d H:i', $file['updatedTime']);
            $file['fileSize'] = FileToolkit::formatFileSize($file['fileSize']);

            // Delete some file attributes to redunce the json response size
            unset($file['hashId']);
            unset($file['convertHash']);
            unset($file['etag']);
            unset($file['convertParams']);

            unset($file);
        }

        if (!empty($paginator)) {
            $paginator = Paginator::toArray($paginator);

            return $this->createJsonResponse(array(
                'files' => $files,
                'paginator' => $paginator,
            ));
        } else {
            return $this->createJsonResponse($files);
        }
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->getBiz()->service('CloudPlatform:AppService');
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->getBiz()->service('OpenCourse:OpenCourseService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->getBiz()->service('Course:MaterialService');
    }

    protected function getCourseDraftService()
    {
        return $this->getBiz()->service('Course:CourseDraftService');
    }
}
