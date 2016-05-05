<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\FileToolkit;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CourseFileManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $type = $request->query->get('type');
        $type = in_array($type, array('courselesson', 'coursematerial')) ? $type : 'courselesson';

        $conditions = array(
            'targetType' => $type,
            'targetId'   => $course['id']
        );

        if (array_key_exists('targetId', $conditions) && !empty($conditions['targetId'])) {
            $course = $this->getCourseService()->getCourse($conditions['targetId']);

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
            $useNum            = $this->getCourseService()->searchLessonCount(array('mediaId' => $file['id']));
            $manageFilesUseNum = $this->getMaterialService()->getMaterialCountByFileId($file['id']);

            if ($files[$key]['targetType'] == 'coursematerial') {
                $files[$key]['useNum'] = $manageFilesUseNum;
            } else {
                $files[$key]['useNum'] = $useNum;
            }
        }

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($files, 'updatedUserId'));

        return $this->render('TopxiaWebBundle:CourseFileManage:index.html.twig', array(
            'type'      => $type,
            'course'    => $course,
            'files'     => $files,
            'users'     => ArrayToolkit::index($users, 'id'),
            'paginator' => $paginator,
            'now'       => time()
        ));
    }

    public function fileStatusAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            return $this->createJsonResponse(array());
        }

        $fileIds = $request->request->get('ids');

        if (empty($fileIds)) {
            return $this->createJsonResponse(array());
        }

        $fileIds = explode(',', $fileIds);

        return $this->createJsonResponse($this->getUploadFileService()->findCloudFilesByIds($fileIds));
    }

    public function showAction(Request $request, $id, $fileId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
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
            $course = $this->getCourseService()->tryManageCourse($id);
        }

        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        $convertHash = $this->getUploadFileService()->reconvertFile2($file['id']);

        if (empty($convertHash)) {
            return $this->createJsonResponse(array('status' => 'error', 'message' => '文件转换请求失败，请重试！'));
        }

        return $this->createJsonResponse(array('status' => 'ok'));
    }

    public function uploadCourseFilesAction(Request $request, $id, $targetType)
    {
        if (!empty($id)) {
            $course = $this->getCourseService()->tryManageCourse($id);
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

    public function deleteCourseFilesAction(Request $request, $id, $type)
    {
        if (!empty($id)) {
            $course = $this->getCourseService()->tryManageCourse($id);
        }

        $ids = $request->request->get('ids', array());

        $this->getUploadFileService()->deleteFiles2($ids);

        return $this->createJsonResponse(true);
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
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

    protected function createPrivateFileDownloadResponse(Request $request, $file)
    {
        $response = BinaryFileResponse::create($file['fullpath'], 200, array(), false);
        $response->trustXSendfileTypeHeader();

        $file['filename'] = urlencode($file['filename']);
        $file['filename'] = str_replace('+', '%20', $file['filename']);

        if (preg_match("/MSIE/i", $request->headers->get('User-Agent'))) {
            $response->headers->set('Content-Disposition', 'attachment; filename="'.$file['filename'].'"');
        } else {
            $response->headers->set('Content-Disposition', "attachment; filename*=UTF-8''".$file['filename']);
        }

        $mimeType = FileToolkit::getMimeTypeByExtension($file['ext']);

        if ($mimeType) {
            $response->headers->set('Content-Type', $mimeType);
        }

        return $response;
    }
}
