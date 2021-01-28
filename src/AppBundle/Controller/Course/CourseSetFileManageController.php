<?php

namespace AppBundle\Controller\Course;

use AppBundle\Common\Paginator;
use AppBundle\Common\FileToolkit;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\Course\MaterialException;
use Biz\File\UploadFileException;
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
        $subtitles = $this->getSubtitleService()->findSubtitlesByMediaIds($fileIds);
        if (!empty($subtitles)) {
            $subtitles = ArrayToolkit::index($subtitles, 'mediaId');
        }

        return $this->render('courseset-manage/file/index.html.twig', array(
            'courseSet' => $courseSet,
            'files' => $files,
            'users' => ArrayToolkit::index($users, 'id'),
            'paginator' => $paginator,
            'now' => time(),
            'filesQuote' => $filesQuote,
            'subtitles' => $subtitles,
        ));
    }

    public function detailAction(Request $request, $courseSetId, $fileId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $currentUser = $this->getCurrentUser();
        $materialCount = $this->getMaterialService()->countMaterials(
            array(
                'courseSetId' => $courseSetId,
                'fileId' => $fileId,
            )
        );

        if (!$materialCount) {
            $this->createNewException(MaterialException::NOTFOUND_MATERIAL());
        }

        $file = $this->getUploadFileService()->getFullFile($fileId);

        if ('local' == $file['storage'] || $currentUser['id'] != $file['createdUserId']) {
            $fileTags = $this->getUploadFileTagService()->findByFileId($fileId);
            $tags = $this->getTagService()->findTagsByIds(ArrayToolkit::column($fileTags, 'tagId'));
            $file['tags'] = ArrayToolkit::column($tags, 'name');

            return $this->render('material-lib/web/static-detail.html.twig', array(
                'material' => $file,
                'thumbnails' => '',
                'editUrl' => $this->generateUrl('material_edit', array('fileId' => $file['id'])),
            ));
        } else {
            try {
                if ('video' == $file['type']) {
                    $thumbnails = $this->getCloudFileService()->getDefaultHumbnails($file['globalId']);
                }
            } catch (\RuntimeException $e) {
                $thumbnails = array();
            }

            return $this->render('admin/cloud-file/detail.html.twig', array(
                'material' => $file,
                'thumbnails' => empty($thumbnails) ? '' : $thumbnails,
                'params' => $request->query->all(),
                'editUrl' => $this->generateUrl('material_edit', array('fileId' => $file['id'])),
            ));
        }
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
            $this->createNewException(MaterialException::NOTFOUND_MATERIAL());
        }

        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        return $this->forward('AppBundle:UploadFile:download', array('fileId' => $file['id']));
    }

    public function convertAction($id, $fileId)
    {
        $this->getCourseSetService()->tryManageCourseSet($id);

        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        $convertHash = $this->getUploadFileService()->reconvertFile($file['id']);

        if (empty($convertHash)) {
            return $this->createJsonResponse(array('status' => 'error', 'message' => '文件转换请求失败，请重试！'));
        }

        return $this->createJsonResponse(array('status' => 'ok'));
    }

    public function retryTranscodeAction($id, $fileId)
    {
        $this->getCourseSetService()->tryManageCourseSet($id);

        $file = $this->getUploadFileService()->getFile($fileId);

        if (empty($file)) {
            $this->createNewException(UploadFileException::NOTFOUND_FILE());
        }

        if (in_array($file['audioConvertStatus'], array('none', 'error'))) {
            $convertStatus = $this->getUploadFileService()->retryTranscode(array($file['globalId']));
            if (empty($convertStatus)) {
                return $this->createJsonResponse(array('status' => 'error', 'message' => '文件转换请求失败，请重试！'));
            }
            if (isset($convertStatus['error'])) {
                return $this->createJsonResponse(array('status' => 'error', 'message' => $convertStatus['error']));
            }
            if (isset($convertStatus['status']) && 'ok' == $convertStatus['status']) {
                $this->getUploadFileService()->setAudioConvertStatus($fileId, 'doing');
            }
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

            if (empty($formData['ids'])) {
                $this->createNewException(CommonException::ERROR_PARAMETER());
            }

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
        $this->createNewException(CommonException::NOT_ALLOWED_METHOD());
    }

    public function batchTagAddAction(Request $request, $id)
    {
        $this->getCourseSetService()->tryManageCourseSet($id);

        $data = $request->request->all();
        $fileIds = preg_split('/,/', $data['fileIds']);

        $this->getMaterialLibService()->batchTagEdit($fileIds, $data['tags']);

        return $this->redirect($this->generateUrl('course_set_manage_files', array('id' => $id)));
    }

    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLibService');
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

    protected function getSubtitleService()
    {
        return $this->getBiz()->service('Subtitle:SubtitleService');
    }

    protected function getUploadFileTagService()
    {
        return $this->createService('File:UploadFileTagService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    protected function getCloudFileService()
    {
        return $this->createService('CloudFile:CloudFileService');
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
