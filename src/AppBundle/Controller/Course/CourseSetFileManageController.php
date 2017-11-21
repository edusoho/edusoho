<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\Paginator;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\ArrayToolkit;
use Biz\System\Service\SettingService;
use Biz\Course\Service\MaterialService;
use Biz\File\Service\UploadFileService;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CourseSetFileManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $sync = $request->query->get('sync');
        if ($courseSet['locked'] && empty($sync)) {
            return $this->redirectToRoute('course_set_manage_sync', array(
                'id' => $id,
                'sideNav' => 'files',
            ));
        }

        $conditions = array(
            'courseSetId' => $courseSet['id'],
            'type' => 'course',
        );
        // XXX
        // if ($courseSet['parentId'] > 0 && $courseSet['locked'] == 1) {
        //     $conditions['courseSetId'] = $courseSet['parentId'];
        // }

        $paginator = new Paginator(
            $request,
            $this->getMaterialService()->searchMaterialCountGroupByFileId($conditions),
            20
        );

        $fileIds = $this->getMaterialService()->searchFileIds(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $files = $this->getUploadFileService()->findFilesByIds($fileIds, $showCloud = 1);
        usort($files, function ($f1, $f2) {
            if (empty($f1['updatedTime']) || empty($f2['updatedTime'])) {
                return $f1['createdTime'] < $f2['createdTime'];
            }

            return $f1['updatedTime'] < $f2['updatedTime'];
        });

        //XXX 暂不考虑公开课
        $filesQuote = $this->getMaterialService()->findUsedCourseSetMaterials($fileIds, $id);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($files, 'updatedUserId'));

        return $this->render('courseset-manage/file/index.html.twig', array(
            'courseSet' => $courseSet,
            'files' => $files,
            'users' => ArrayToolkit::index($users, 'id'),
            'paginator' => $paginator,
            'now' => time(),
            'filesQuote' => $filesQuote,
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

    public function showAction($id, $fileId)
    {
        $this->getCourseSetService()->tryManageCourseSet($id);

        $materialCount = $this->getMaterialService()->countMaterials(
            array(
                'courseSetId' => $id,
                'fileId' => $fileId,
            )
        );

        if (!$materialCount) {
            throw $this->createNotFoundException('Materials Not Found');
        }

        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            throw $this->createNotFoundException('File Not Found');
        }

        return $this->forward('AppBundle:UploadFile:download', array('fileId' => $file['id']));
    }

    public function convertAction($id, $fileId)
    {
        $this->getCourseSetService()->tryManageCourseSet($id);

        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            throw $this->createNotFoundException('File Not Found');
        }

        $convertHash = $this->getUploadFileService()->reconvertFile($file['id']);

        if (empty($convertHash)) {
            return $this->createJsonResponse(array('status' => 'error', 'message' => '文件转换请求失败，请重试！'));
        }

        return $this->createJsonResponse(array('status' => 'ok'));
    }

    public function deleteMaterialsAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $fileIds = $request->request->get('ids');
        $materials = $this->getMaterialService()->findUsedCourseMaterials($fileIds, $id);
        $files = $this->getUploadFileService()->findFilesByIds($fileIds, 0);
        $files = ArrayToolkit::index($files, 'id');

        return $this->render('courseset-manage/file/file-delete-modal.html.twig', array(
            'courseSet' => $courseSet,
            'materials' => $materials,
            'files' => $files,
            'ids' => $fileIds,
        ));
    }

    public function deleteCourseFilesAction(Request $request, $id)
    {
        $this->getCourseSetService()->tryManageCourseSet($id);

        if ('POST' == $request->getMethod()) {
            $formData = $request->request->all();

            $deletedMaterials = $this->getMaterialService()->deleteMaterials($id, $formData['ids']);

            if (empty($deletedMaterials)) {
                return $this->createJsonResponse(true);
            }

            if (!empty($formData['isDeleteFile'])) {
                $fileIds = array_unique(ArrayToolkit::column($deletedMaterials, 'fileId'));
                foreach ($fileIds as $fileId) {
                    if ($this->getUploadFileService()->canManageFile($fileId)) {
                        $this->getUploadFileService()->deleteFile($fileId);
                    }
                }
            }

            return $this->createJsonResponse(true);
        }
        throw $this->createAccessDeniedException('Method Not Allowed');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->getBiz()->service('File:UploadFileService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return MaterialService
     */
    protected function getMaterialService()
    {
        return $this->getBiz()->service('Course:MaterialService');
    }

    protected function createPrivateFileDownloadResponse(Request $request, $file)
    {
        $response = BinaryFileResponse::create($file['fullpath'], 200, array(), false);
        $response->trustXSendfileTypeHeader();

        $file['filename'] = urlencode($file['filename']);
        $file['filename'] = str_replace('+', '%20', $file['filename']);

        if (preg_match('/MSIE/i', $request->headers->get('User-Agent'))) {
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
