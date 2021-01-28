<?php

namespace AppBundle\Controller\Admin;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use Biz\CloudFile\Service\CloudFileService;
use Biz\CloudPlatform\CloudAPIFactory;
use Symfony\Component\HttpFoundation\Request;

class CloudFileController extends BaseController
{
    public function indexAction()
    {
        try {
            $api = CloudAPIFactory::create('leaf');
            $result = $api->get('/me');
        } catch (\RuntimeException $e) {
            return $this->render('admin/cloud-file/api-error.html.twig', array());
        }

        $storageSetting = $this->getSettingService()->get('storage', array());

        if (isset($result['hasStorage']) && '1' == $result['hasStorage'] && 'cloud' == $storageSetting['upload_mode']) {
            return $this->redirect($this->generateUrl('admin_cloud_file_manage'));
        }

        return $this->render('admin/cloud-file/error.html.twig', array());
    }

    public function manageAction(Request $request)
    {
        $storageSetting = $this->getSettingService()->get('storage', array());

        if ('cloud' != $storageSetting['upload_mode']) {
            return $this->redirect($this->generateUrl('admin_cloud_file'));
        }

        return $this->render('admin/cloud-file/manage.html.twig', array(
            'tags' => $this->getTagService()->findAllTags(0, PHP_INT_MAX),
        ));
    }

    public function renderAction(Request $request)
    {
        $conditions = $request->query->all();
        //云资源应该只显示resType为normal的
        $conditions['storage'] = 'cloud';
        $results = $this->getCloudFileService()->search(
            $conditions,
            ($request->query->get('page', 1) - 1) * 20,
            20
        );

        $paginator = new Paginator(
            $request,
            $results['count'],
            20
        );
        $pageType = (isset($conditions['resType']) && 'attachment' == $conditions['resType']) ? 'attachment' : 'file';

        return $this->render('admin/cloud-file/tbody.html.twig', array(
            'pageType' => $pageType,
            'type' => empty($conditions['type']) ? 'all' : $conditions['type'],
            'materials' => $results['data'],
            'createdUsers' => isset($results['createdUsers']) ? $results['createdUsers'] : array(),
            'paginator' => $paginator,
        ));
    }

    public function previewAction(Request $reqeust, $globalId)
    {
        $file = $this->getCloudFileService()->getByGlobalId($globalId);

        return $this->render('admin/cloud-file/preview-modal.html.twig', array(
            'file' => $file,
        ));
    }

    public function detailAction(Request $reqeust, $globalId)
    {
        try {
            if (!$globalId) {
                return $this->render('admin/cloud-file/detail-not-found.html.twig', array());
            }

            $cloudFile = $this->getCloudFileService()->getByGlobalId($globalId);
        } catch (\RuntimeException $e) {
            return $this->render('admin/cloud-file/detail-not-found.html.twig', array());
        }

        try {
            if ('video' == $cloudFile['type']) {
                $thumbnails = $this->getCloudFileService()->getDefaultHumbnails($globalId);
            }
        } catch (\RuntimeException $e) {
            $thumbnails = array();
        }

        return $this->render('admin/cloud-file/detail.html.twig', array(
            'material' => $cloudFile,
            'thumbnails' => empty($thumbnails) ? '' : $thumbnails,
            'params' => $reqeust->query->all(),
            'editUrl' => $this->generateUrl('admin_cloud_file_edit', array('globalId' => $globalId)),
        ));
    }

    public function editAction(Request $request, $globalId, $fields)
    {
        $fields = $request->request->all();

        $result = $this->getCloudFileService()->edit($globalId, $fields);

        return $this->createJsonResponse($result);
    }

    public function reconvertAction(Request $request, $globalId)
    {
        $cloudFile = $this->getCloudFileService()->reconvert($globalId, array(
            'directives' => array(),
        ));

        if (isset($cloudFile['createdUserId'])) {
            $createdUser = $this->getUserService()->getUser($cloudFile['createdUserId']);
        }

        return $this->render('admin/cloud-file/table-tr.html.twig', array(
            'cloudFile' => $cloudFile,
            'createdUser' => isset($createdUser) ? $createdUser : array(),
        ));
    }

    public function downloadAction($globalId)
    {
        $download = $this->getCloudFileService()->download($globalId);

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
            array('globalIds' => $globalIds),
            array('createdTime' => 'desc'),
            0, PHP_INT_MAX
        );

        $materials = array();
        if ($files) {
            $files = ArrayToolkit::index($files, 'id');
            $fileIds = ArrayToolkit::column($files, 'id');
            $materials = $this->getCourseMaterialService()->findUsedCourseMaterials($fileIds, $courseId = 0);
        }

        return $this->render('material-lib/web/delete-file-modal.html.twig', array(
            'materials' => $materials,
            'files' => $files,
            'ids' => $globalIds,
            'deleteFormUrl' => $this->generateUrl('admin_cloud_file_batch_delete'),
        ));
    }

    public function batchTagShowAction(Request $request)
    {
        $data = $request->request->all();
        $fileIds = preg_split('/,/', $data['fileIds']);

        $this->getMaterialLibService()->batchTagEdit($fileIds, $data['tags']);

        return $this->redirect($this->generateUrl('admin_cloud_file_manage'));
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

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

    protected function getUploadFileService()
    {
        return $this->createService('File:UploadFileService');
    }

    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLibService');
    }

    protected function getCourseMaterialService()
    {
        return $this->createService('Course:MaterialService');
    }
}
