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

        $type = $request->query->get('type');
        $type = 'opencourselesson';

        $conditions = array(
            'targetType' => $type,
            'targetId'   => $course['id']
        );

        if (array_key_exists('targetId', $conditions) && !empty($conditions['targetId'])) {
            $course = $this->getOpenCourseService()->getCourse($conditions['targetId']);

            if ($course['parentId'] > 0 && $course['locked'] == 1) {
                $conditions['targetId'] = $course['parentId'];
            }
        }

        $paginator = new Paginator(
            $request,
            $this->getUploadFileService()->searchFileCount($conditions),
            20
        );

        $files = $this->getUploadFileService()->searchFiles(
            $conditions,
            'latestCreated',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        foreach ($files as $key => $file) {
            $files[$key]['metas2'] = json_decode($file['metas2'], true) ?: array();

            $files[$key]['convertParams'] = json_decode($file['convertParams']) ?: array();

            $useNum = $this->getOpenCourseService()->searchLessonCount(array('mediaId' => $file['id']));

            $manageFilesUseNum = $this->getMaterialService()->getMaterialCountByFileId($file['id']);

            $files[$key]['useNum'] = $useNum;
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($files, 'updatedUserId'));

        $storageSetting = $this->getSettingService()->get("storage");
        return $this->render('TopxiaWebBundle:CourseFileManage:index.html.twig', array(
            'type'           => $type,
            'course'         => $course,
            'courseLessons'  => $files,
            'users'          => ArrayToolkit::index($users, 'id'),
            'paginator'      => $paginator,
            'now'            => time(),
            'storageSetting' => $storageSetting
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
        if ($id != 0) {
            $course = $this->getOpenCourseService()->tryManageOpenCourse($id);
        }

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
        if (!empty($id)) {
            $course = $this->getOpenCourseService()->tryManageOpenCourse($id);
        } else {
            $course = null;
        }

        $storageSetting = $this->getSettingService()->get('storage', array());
        return $this->render('TopxiaWebBundle:CourseFileManage:modal-upload-course-files.html.twig', array(
            'course'         => $course,
            'storageSetting' => $storageSetting,
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

        $storageSetting = $this->getSettingService()->get('storage', array());
        $fileExts       = "";

        if ("courselesson" == $targetType) {
            $fileExts = "*.mp3;*.mp4;*.avi;*.flv;*.wmv;*.mov;*.mpg;*.ppt;*.pptx;*.doc;*.docx;*.pdf;*.swf";
        }

        return $this->render('TopxiaWebBundle:CourseFileManage:batch-upload.html.twig', array(
            'course'         => $course,
            'storageSetting' => $storageSetting,
            'targetType'     => $targetType,
            'targetId'       => $id,
            'fileExts'       => $fileExts
        ));
    }

    public function deleteCourseFilesAction(Request $request, $id, $type)
    {
        if (!empty($id)) {
            $course = $this->getOpenCourseService()->tryManageOpenCourse($id);
        }

        $ids = $request->request->get('ids', array());

        $this->getUploadFileService()->deleteFiles($ids);

        return $this->createJsonResponse(true);
    }

    protected function getOpenCourseService()
    {
        return $this->getServiceKernel()->createService('OpenCourse.OpenCourseService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getMaterialService()
    {
        return $this->getServiceKernel()->createService('Course.MaterialService');
    }
}
