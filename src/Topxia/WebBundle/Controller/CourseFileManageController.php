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
        $course     = $this->getCourseService()->tryManageCourse($id);
        $conditions = array(
            'courseId' => $course['id'],
            'type'     => 'course'
        );

        if ($course['parentId'] > 0 && $course['locked'] == 1) {
            $conditions['courseId'] = $course['parentId'];
        }

        $paginator = new Paginator(
            $request,
            $this->getMaterialService()->searchMaterialCountGroupByFileId($conditions),
            20
        );

        $files = $this->getMaterialService()->searchMaterialsGroupByFileId(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $files      = $this->_materialsSort($files);
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

        return $this->createJsonResponse($this->getUploadFileService()->findFilesByIds($fileIds, 1));
    }

    public function showAction(Request $request, $id, $fileId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $materialCount = $this->getMaterialService()->searchMaterialCount(
            array(
                'courseId' => $id,
                'fileId'   => $fileId
            )
        );

        if (!$materialCount) {
            throw $this->createNotFoundException();
        }

        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        return $this->forward('TopxiaWebBundle:UploadFile:download', array('fileId' => $file['id']));
    }

    public function convertAction(Request $request, $id, $fileId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            throw $this->createNotFoundException();
        }

        $convertHash = $this->getUploadFileService()->reconvertFile($file['id']);

        if (empty($convertHash)) {
            return $this->createJsonResponse(array('status' => 'error', 'message' => '文件转换请求失败，请重试！'));
        }

        return $this->createJsonResponse(array('status' => 'ok'));
    }

    public function uploadCourseFilesAction(Request $request, $id, $targetType)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        $storageSetting = $this->getSettingService()->get('storage', array());
        return $this->render('TopxiaWebBundle:CourseFileManage:modal-upload-course-files.html.twig', array(
            'course'         => $course,
            'storageSetting' => $storageSetting,
            'targetType'     => $targetType,
            'targetId'       => $id
        ));
    }

    public function deleteMaterialShowAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

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

    public function deleteCourseFilesAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();

            $this->getMaterialService()->deleteMaterials($id, $formData['ids']);

            if (isset($formData['isDeleteFile']) && $formData['isDeleteFile']) {
                foreach ($formData['ids'] as $key => $fileId) {
                    if ($this->getUploadFileService()->canManageFile($fileId)) {
                        $this->getUploadFileService()->deleteFile($fileId);
                    }
                }
            }

            return $this->createJsonResponse(true);
        }
    }

    private function _materialsSort($materials)
    {
        if (!$materials) {
            return array();
        }

        $fileIds = ArrayToolkit::column($materials, 'fileId');
        $files   = $this->getUploadFileService()->findFilesByIds($fileIds, $showCloud = 1);

        $files     = ArrayToolkit::index($files, 'id');
        $sortFiles = array();
        foreach ($materials as $key => $material) {
            if (isset($files[$material['fileId']])) {
                $file            = array_merge($material, $files[$material['fileId']]);
                $sortFiles[$key] = $file;
            }
        }

        return $sortFiles;
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
