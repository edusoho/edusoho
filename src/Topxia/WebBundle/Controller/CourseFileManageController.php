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

/*        $type = $request->query->get('type');
        $type = in_array($type, array('courselesson', 'coursematerial')) ? $type : 'courselesson';
*/
        $conditions = array(
            'targetTypes' => array('courselesson', 'coursematerial'),
            'targetId'    => $course['id']
        );

        if ($course['parentId'] > 0 && $course['locked'] == 1) {
            $conditions['targetId'] = $course['parentId'];
        }

        $courseMaterials = $this->getMaterialService()->findCourseMaterials($conditions['targetId'], 0, PHP_INT_MAX);
        if ($courseMaterials) {
            $conditions['idsOr'] = array_unique(ArrayToolkit::column($courseMaterials,'fileId'));
        }

        $paginator = new Paginator(
            $request,
            $this->getUploadFileService()->searchFileCount($conditions),
            20
        );

        $files = $this->getUploadFileService()->searchFiles(
            $conditions,
            array('createdTime','DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $fileIds    = ArrayToolkit::column($files, 'id');
        $filesQuote = $this->getMaterialService()->findCourseMaterialsQuotes($id, $fileIds);

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

        $convertHash = $this->getUploadFileService()->reconvertFile($file['id']);

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

    public function deleteCourseFilesAction(Request $request, $id)
    {
        $course = $this->getCourseService()->tryManageCourse($id);

        if ($request->getMethod() == 'POST') {

            $formData = $request->request->all();

            if (isset($formData['isDeleteFile']) && $formData['isDeleteFile']) {
                foreach ($formData['ids'] as $key => $fileId) {
                    if ($this->getUploadFileService()->canManageFile($fileId)) {
                        $this->getUploadFileService()->deleteFile($fileId);
                    }
                }
            } 
            
            $this->getMaterialService()->deleteMaterials($id, $formData['ids']);

            return $this->createJsonResponse(true);
        }
        
        return $this->render('TopxiaWebBundle:CourseFileManage:file-delete-modal.html.twig', array(
            'course' => $course,
        ));
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
