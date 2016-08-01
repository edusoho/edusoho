<?php

namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class OpenCourseFileManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $conditions = array(
            'courseId' => $course['id'],
            'type'     => 'openCourse'
        );

        $paginator = new Paginator(
            $request,
            $this->getMaterialService()->searchMaterialCountGroupByFileId($conditions),
            20
        );

        $materials = $this->getMaterialService()->searchMaterialsGroupByFileId(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $files      = $this->getMaterialService()->findFullFilesAndSort($materials);
        $fileIds    = ArrayToolkit::column($files, 'fileId');
        $filesQuote = $this->getMaterialService()->findUsedCourseMaterials($fileIds, $id);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($files, 'updatedUserId'));

        return $this->render('TopxiaWebBundle:CourseFileManage:index.html.twig', array(
            'course'     => $course,
            'files'      => $files,
            'users'      => ArrayToolkit::index($users, 'id'),
            'paginator'  => $paginator,
            'now'        => time(),
            'filesQuote' => $filesQuote
        ));
    }

    public function showAction(Request $request, $id, $fileId)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);
        $file   = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        if ($id != $file["targetId"]) {
            throw $this->createNotFoundException();
        }

        return $this->forward('TopxiaWebBundle:UploadFile:download', array('fileId' => $file['id']));
    }

    public function convertAction(Request $request, $id, $fileId)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        $convertHash = $this->getUploadFileService()->reconvertFile(
            $file['id'],
            $this->generateUrl('uploadfile_cloud_convert_callback2', array(), true)
        );

        if (empty($convertHash)) {
            return $this->createJsonResponse(array('status' => 'error', 'message' => '文件转换请求失败，请重试！'));
        }

        return $this->createJsonResponse(array('status' => 'ok'));
    }

    public function uploadCourseFilesAction(Request $request, $id, $targetType)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        return $this->render('TopxiaWebBundle:CourseFileManage:modal-upload-course-files.html.twig', array(
            'course'         => $course,
            'storageSetting' => $this->setting('storage', array()),
            'targetType'     => $targetType,
            'targetId'       => $id
        ));
    }

    public function batchUploadCourseFilesAction(Request $request, $id, $targetType)
    {
        if ("materiallib" != $targetType) {
            $course = $this->getOpenCourseService()->tryManageOpenCourse($id);
        } else {
            $course = null;
        }

        $fileExts = '';

        if ('opencourselesson' == $targetType) {
            $fileExts = "*.mp3;*.mp4;*.avi;*.flv;*.wmv;*.mov;*.mpg;*.ppt;*.pptx;*.doc;*.docx;*.pdf;*.swf";
        }

        return $this->render('TopxiaWebBundle:CourseFileManage:batch-upload.html.twig', array(
            'course'         => $course,
            'storageSetting' => $this->setting('storage', array()),
            'targetType'     => $targetType,
            'targetId'       => $id,
            'fileExts'       => $fileExts
        ));
    }

    public function deleteCourseFilesAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();

            $this->getMaterialService()->deleteMaterials($id, $formData['ids'], 'openCourse');

            if (isset($formData['isDeleteFile']) && $formData['isDeleteFile']) {
                foreach ($formData['ids'] as $key => $fileId) {
                    if ($this->getUploadFileService()->canManageFile($fileId)) {
                        $this->getUploadFileService()->deleteFile($fileId);
                    }
                }
            }

            return $this->createJsonResponse(true);
        }

        return $this->render('TopxiaWebBundle:CourseFileManage:file-delete-modal.html.twig', array(
            'course' => $course
        ));
    }

    public function deleteMaterialShowAction(Request $request, $id)
    {
        $course = $this->getOpenCourseService()->tryManageOpenCourse($id);

        $fileIds   = $request->request->get('ids');
        $materials = $this->getMaterialService()->findUsedCourseMaterials($fileIds, $id);
        $files     = $this->getUploadFileService()->findFilesByIds($fileIds, 0);
        $files     = ArrayToolkit::index($files, 'id');

        return $this->render('TopxiaWebBundle:CourseFileManage:file-delete-modal.html.twig', array(
            'course'    => $course,
            'materials' => $materials,
            'files'     => $files,
            'ids'       => $fileIds
        ));
    }

    public function lessonMaterialModalAction(Request $request, $courseId, $lessonId)
    {
        $course = $this->getOpenCourseService()->tryManageCourse($courseId);
        $lesson = $this->getOpenCourseService()->getCourseLesson($courseId, $lessonId);

        $materials = $this->getMaterialService()->searchMaterials(
            array('lessonId' => $lesson['id'], 'type' => 'openCourse'),
            array('createdTime', 'DESC'),
            0, 100
        );
        return $this->render('TopxiaWebBundle:CourseMaterialManage:material-modal.html.twig', array(
            'course'         => $course,
            'lesson'         => $lesson,
            'materials'      => $materials,
            'storageSetting' => $this->setting('storage'),
            'targetType'     => 'coursematerial',
            'targetId'       => $course['id']
        ));
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }
}
