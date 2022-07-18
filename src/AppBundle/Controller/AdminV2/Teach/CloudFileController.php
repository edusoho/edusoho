<?php

namespace AppBundle\Controller\AdminV2\Teach;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudFile\Service\CloudFileService;
use Biz\CloudPlatform\CloudAPIFactory;
use Biz\Course\Service\MaterialService;
use Biz\File\Service\UploadFileService;
use Biz\MaterialLib\Service\MaterialLibService;
use Biz\System\Service\SettingService;
use Biz\Taxonomy\Service\TagService;
use Symfony\Component\HttpFoundation\Request;

class CloudFileController extends BaseController
{
    public function indexAction()
    {
        try {
            $api = CloudAPIFactory::create('leaf');
            $result = $api->get('/me');
            if (empty($result['accessCloud'])) {
                return $this->render('admin-v2/cloud-center/edu-cloud/not-access.html.twig', ['menu' => 'admin_v2_cloud_file']);
            }
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/teach/cloud-file/api-error.html.twig', []);
        }

        $storageSetting = $this->getSettingService()->get('storage', []);

        if (isset($result['hasStorage']) && '1' == $result['hasStorage'] && 'cloud' == $storageSetting['upload_mode']) {
            return $this->redirect($this->generateUrl('admin_v2_cloud_file_manage'));
        }

        return $this->render('admin-v2/teach/cloud-file/error.html.twig', []);
    }

    public function manageAction(Request $request)
    {
        $storageSetting = $this->getSettingService()->get('storage', []);

        if ('cloud' != $storageSetting['upload_mode']) {
            return $this->redirect($this->generateUrl('admin_v2_cloud_file'));
        }

        return $this->render('admin-v2/teach/cloud-file/manage.html.twig', [
            'tags' => $this->getTagService()->findAllTags(0, PHP_INT_MAX),
        ]);
    }

    public function attachmentListAction(Request $request)
    {
        try {
            $api = CloudAPIFactory::create('leaf');
            $result = $api->get('/me');
            if (empty($result['accessCloud'])) {
                return $this->render('admin-v2/cloud-center/edu-cloud/not-access.html.twig', ['menu' => 'admin_v2_cloud_attachment']);
            }
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/teach/cloud-attachment/api-error.html.twig', []);
        }

        $storageSetting = $this->getSettingService()->get('storage', []);

        if (isset($result['hasStorage']) && '1' == $result['hasStorage'] && 'cloud' == $storageSetting['upload_mode']) {
            return $this->render('admin-v2/teach/cloud-attachment/index.html.twig');
        }

        return $this->render('admin-v2/teach/cloud-attachment/error.html.twig', []);
    }

    public function questionBankAttachmentListAction(Request $request)
    {
        try {
            $api = CloudAPIFactory::create('leaf');
            $result = $api->get('/me');
            if (empty($result['accessCloud'])) {
                return $this->render('admin-v2/cloud-center/edu-cloud/not-access.html.twig', ['menu' => 'admin_v2_cloud_attachment']);
            }
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/teach/cloud-attachment/api-error.html.twig', []);
        }

        $storageSetting = $this->getSettingService()->get('storage', []);

        if (isset($result['hasStorage']) && '1' == $result['hasStorage'] && 'cloud' == $storageSetting['upload_mode']) {
            return $this->render('admin-v2/teach/question-bank-attachment/index.html.twig');
        }

        return $this->render('admin-v2/teach/cloud-attachment/error.html.twig', []);
    }

    public function livePlaybackAction(Request $request)
    {
        return $this->render('admin-v2/teach/cloud-resources/live-playback.html.twig');
    }

    public function renderAction(Request $request)
    {
        $conditions = $request->query->all();
        //云资源应该只显示resType为normal的
        $conditions['storage'] = 'cloud';

        if (isset($conditions['type']) && 'other' == $conditions['type']) {
            $conditions['types'] = ['other', 'flash'];
            unset($conditions['type']);
        }

        $results = $this->getCloudFileService()->search(
            $conditions,
            ($request->query->get('page', 1) - 1) * 20,
            20
        );

        $paginator = new Paginator(
            $this->get('request'),
            $results['count'],
            20
        );
        $pageType = (isset($conditions['resType']) && 'attachment' == $conditions['resType']) ? 'attachment' : 'file';

        return $this->render('admin-v2/teach/cloud-file/tbody.html.twig', [
            'pageType' => $pageType,
            'type' => empty($conditions['type']) ? 'all' : $conditions['type'],
            'materials' => $results['data'],
            'createdUsers' => isset($results['createdUsers']) ? $results['createdUsers'] : [],
            'paginator' => $paginator,
        ]);
    }

    public function previewAction(Request $reqeust, $globalId)
    {
        $file = $this->getCloudFileService()->getByGlobalId($globalId);

        return $this->render('admin-v2/teach/cloud-file/preview-modal.html.twig', [
            'file' => $file,
            'type' => $type ?? '',
        ]);
    }

    public function detailAction(Request $reqeust, $globalId)
    {
        try {
            if (!$globalId) {
                return $this->render('admin-v2/teach/cloud-file/detail-not-found.html.twig', []);
            }

            $cloudFile = $this->getCloudFileService()->getByGlobalId($globalId);
        } catch (\RuntimeException $e) {
            return $this->render('admin-v2/teach/cloud-file/detail-not-found.html.twig', []);
        }

        try {
            if ('video' == $cloudFile['type']) {
                $thumbnails = $this->getCloudFileService()->getDefaultHumbnails($globalId);
            }
        } catch (\RuntimeException $e) {
            $thumbnails = [];
        }

        return $this->render('admin-v2/teach/cloud-file/detail.html.twig', [
            'material' => $cloudFile,
            'thumbnails' => empty($thumbnails) ? '' : $thumbnails,
            'params' => $reqeust->query->all(),
            'editUrl' => $this->generateUrl('admin_v2_cloud_file_edit', ['globalId' => $globalId]),
        ]);
    }

    public function editAction(Request $request, $globalId, $fields)
    {
        $fields = $request->request->all();

        $result = $this->getCloudFileService()->edit($globalId, $fields);

        return $this->createJsonResponse($result);
    }

    public function reconvertAction(Request $request, $globalId)
    {
        $cloudFile = $this->getCloudFileService()->reconvert($globalId, [
            'directives' => [],
        ]);

        if (isset($cloudFile['createdUserId'])) {
            $createdUser = $this->getUserService()->getUser($cloudFile['createdUserId']);
        }

        return $this->render('admin-v2/teach/cloud-file/table-tr.html.twig', [
            'cloudFile' => $cloudFile,
            'createdUser' => isset($createdUser) ? $createdUser : [],
        ]);
    }

    public function downloadAction(Request $request, $globalId)
    {
        $ssl = $request->isSecure() ? true : false;
        $download = $this->getCloudFileService()->download($globalId, $ssl);

        return $this->redirect($download['url']);
    }

    public function deleteAction($globalId)
    {
        $result = $this->getCloudFileService()->delete($globalId);

        return $this->createJsonResponse($result);
    }

    public function batchDeleteAction(Request $request)
    {
        $data = $request->request->all();

        if (isset($data['ids']) && !empty($data['ids'])) {
            $this->getCloudFileService()->batchDelete($data['ids']);

            return $this->createJsonResponse(true);
        }

        return $this->createJsonResponse(false);
    }

    public function deleteShowAction(Request $request)
    {
        $globalIds = $request->request->get('ids');
        $files = $this->getUploadFileService()->searchFiles(
            ['globalIds' => $globalIds],
            ['createdTime' => 'desc'],
            0,
            PHP_INT_MAX
        );

        $materials = [];
        if ($files) {
            $files = ArrayToolkit::index($files, 'id');
            $fileIds = ArrayToolkit::column($files, 'id');
            $materials = $this->getCourseMaterialService()->findUsedCourseMaterials($fileIds, $courseId = 0);
        }

        return $this->render('material-lib/web/delete-file-modal.html.twig', [
            'materials' => $materials,
            'files' => $files,
            'ids' => $globalIds,
            'deleteFormUrl' => $this->generateUrl('admin_v2_cloud_file_batch_delete'),
        ]);
    }

    public function deleteQuestionFileShowAction(Request $request)
    {
        $globalIds = $request->request->get('ids');
        $files = $this->getUploadFileService()->searchFiles(
            ['globalIds' => $globalIds],
            ['createdTime' => 'desc'],
            0,
            PHP_INT_MAX
        );

        $materials = [];
        if ($files) {
            $files = ArrayToolkit::index($files, 'id');
            $fileIds = ArrayToolkit::column($files, 'id');
            $materials = $this->getCourseMaterialService()->findUsedCourseMaterials($fileIds, $courseId = 0);
        }

        return $this->render('material-lib/web/delete-question-file-modal.html.twig', [
            'materials' => $materials,
            'files' => $files,
            'ids' => $globalIds,
            'deleteFormUrl' => $this->generateUrl('admin_v2_cloud_file_batch_delete'),
        ]);
    }

    public function batchTagShowAction(Request $request)
    {
        $data = $request->request->all();
        $fileIds = preg_split('/,/', $data['fileIds']);

        $this->getMaterialLibService()->batchTagEdit($fileIds, $data['tags']);

        return $this->redirect($this->generateUrl('admin_v2_cloud_file_manage'));
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return TagService
     */
    protected function getTagService()
    {
        return $this->createService('Taxonomy:TagService');
    }

    /**
     * @return CloudFileService
     */
    protected function getCloudFileService()
    {
        return $this->createService('CloudFile:CloudFileService');
    }

    /**
     * @return UploadFileService
     */
    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    /**
     * @return MaterialLibService
     */
    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLibService');
    }

    /**
     * @return MaterialService
     */
    protected function getCourseMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }
}
