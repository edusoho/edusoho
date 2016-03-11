<?php

namespace MaterialLib\MaterialLibBundle\Controller\Web;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use MaterialLib\MaterialLibBundle\Controller\BaseController;
use Topxia\Common\Paginator;

class MaterialLibController extends BaseController
{
    public function indexAction(Request $request, $type = "all", $viewMode = "thumb", $source = "upload")
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $currentUserId = $currentUser['id'];

        $keyWord = $request->query->get('keyword') ?: "";

        $conditions           = array();
        $conditions['status'] = 'ok';

        if ($type != 'all') {
            $conditions['type'] = $type;
        }

        if (!empty($keyWord)) {
            $conditions['filename'] = $keyWord;
        }

        $conditions['source']        = $source;
        $conditions['currentUserId'] = $currentUserId;

        $paginator = new Paginator($request, $this->getUploadFileService()->searchFilesCount($conditions), 20);

        $files = $this->getUploadFileService()->searchFiles($conditions, array('createdTime', 'DESC'), $paginator->getOffsetCount(), $paginator->getPerPageCount());

        $createdUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($files, 'createdUserId'));


        if ($viewMode == 'thumb') {
            $resultPage = 'MaterialLibBundle:Web:material-thumb-view.html.twig';
        }

        $storageSetting = $this->getSettingService()->get("storage");

        $tags = $this->getTagService()->findAllTags(0, 999);

        return $this->render($resultPage, array(
            'currentUserId'  => $currentUserId,
            'type'           => $type,
            'files'          => $files,
            'createdUsers'   => $createdUsers,
            'paginator'      => $paginator,
            'storageSetting' => $storageSetting,
            'viewMode'       => $viewMode,
            'source'         => $source,
            'now'            => time(),
            'tags'           => $tags
        ));
    }

    public function showMyMaterialLibFormAction(Request $request, $viewMode = "thumb")
    {
        $currentUser = $this->getCurrentUser();
        $currentUserId = $currentUser['id'];
        $data          = $request->query->all();
        $source        = $data['source'];
        $type          = $data['type'];
        $keyWord       = $request->query->get('keyword') ?: "";

        $conditions           = array();
        $conditions['status'] = 'ok';

        if ($type != 'all') {
            $conditions['type'] = $type;
        }

        if (!empty($keyWord)) {
            $conditions['filename'] = $keyWord;
        }

        $conditions['source']        = $source;
        $conditions['currentUserId'] = $currentUserId;

        $paginator = new Paginator($request, $this->getUploadFileService()->searchFilesCount($conditions), 20);

        $files = $this->getUploadFileService()->searchFiles($conditions, array('createdTime', 'DESC'), $paginator->getOffsetCount(), $paginator->getPerPageCount());

        $createdUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($files, 'createdUserId'));

        $storageSetting = $this->getSettingService()->get("storage");

        $tags = $this->getTagService()->findAllTags(0, 999);

        return $this->render('MaterialLibBundle:Web/Widget:thumb-list.html.twig', array(
            'currentUserId'  => $currentUserId,
            'type'           => $type,
            'files'          => $files,
            'createdUsers'   => $createdUsers,
            'paginator'      => $paginator,
            'storageSetting' => $storageSetting,
            'viewMode'       => $viewMode,
            'source'         => $source,
            'now'            => time(),
            'tags'           => $tags
        ));
    }

    public function editAction(Request $request, $globalId)
    {
        $fields = $request->request->all();
        $this->getMaterialLibService()->edit($globalId, $fields);
        return $this->createJsonResponse(array('success' => true));
    }

    public function reconvertAction($globalId)
    {
        $this->getMaterialLibService()->reconvert($globalId);
        return $this->createJsonResponse(array('success' => true));
    }

    public function detailAction($globalId)
    {
        if (!$globalId) {
            $this->render('MaterialLibBundle:Web:detail-not-found.html.twig', array(
                'material'   => $material,
                'thumbnails' => $thumbnails
            ));
        }

        $material   = $this->getMaterialLibService()->get($globalId);
        $thumbnails = $this->getMaterialLibService()->getDefaultHumbnails($globalId);
        return $this->render('MaterialLibBundle:Web:detail.html.twig', array(
            'material'   => $material,
            'thumbnails' => $thumbnails
        ));
    }

    public function downloadAction($globalId)
    {
        $download = $this->getMaterialLibService()->download($globalId);
        return $this->redirect($download['url']);
    }

    public function deleteAction($globalId)
    {
        $this->getMaterialLibService()->delete($globalId);
        return $this->createJsonResponse(array('success' => true));
    }

    public function generateThumbnailAction(Request $reqeust, $globalId)
    {
        $second = $reqeust->query->get('second');
        return $this->createJsonResponse($this->getMaterialLibService()->getThumbnail($globalId, array('seconds' => $second)));
    }

    protected function getMaterialLibService()
    {
        return $this->createService('MaterialLib:MaterialLib.MaterialLibService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }
}
