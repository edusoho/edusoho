<?php

namespace MaterialLib\MaterialLibBundle\Controller\Web;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use MaterialLib\MaterialLibBundle\Controller\BaseController;

class MaterialLibController extends BaseController
{
    public function indexAction(Request $request)
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isTeacher() && !$currentUser->isAdmin()) {
            throw $this->createAccessDeniedException('您无权访问此页面');
        }

        $currentUserId = $currentUser['id'];

        $keyWord = $request->query->get('keyword') ?: "";

        $conditions           = $request->query->all();
        $conditions['status'] = 'ok';

        if (empty($conditions['type'])) {
            $conditions['type'] = 'all';
        }

        if (!empty($keyWord)) {
            $conditions['filename'] = $keyWord;
        }

        $conditions['currentUserId'] = $currentUserId;

        return $this->render('MaterialLibBundle:Web:material-thumb-view.html.twig', array(
            'tags' => $this->getTagService()->findAllTags(0, PHP_INT_MAX)
        ));
    }

    public function showMyMaterialLibFormAction(Request $request)
    {
        $currentUser          = $this->getCurrentUser();
        $currentUserId        = $currentUser['id'];
        $conditions           = $request->query->all();
        $conditions['status'] = 'ok';
        if (!empty($conditions['keyword'])) {
            $conditions['filename'] = $conditions['keyword'];
        }

        $conditions['currentUserId'] = $currentUserId;

        $paginator = new Paginator($request, $this->getUploadFileService()->searchFilesCount($conditions), 20);

        $files       = $this->getUploadFileService()->searchFiles($conditions, array('createdTime', 'DESC'), $paginator->getOffsetCount(), $paginator->getPerPageCount());
        $collections = $this->getUploadFileService()->findcollectionsByUserIdAndFileIds(ArrayToolkit::column($files, 'id'), $currentUserId);
        foreach ($files as $key => $file) {
            if (in_array($file['id'], ArrayToolkit::column($collections, 'fileId'))) {
                $files[$key]['collected'] = 1;
            } else {
                $files[$key]['collected'] = 0;
            }
        }
        $createdUsers = $this->getUserService()->findUsersByIds(ArrayToolkit::column($files, 'createdUserId'));

        $storageSetting = $this->getSettingService()->get("storage");

        $tags = $this->getTagService()->findAllTags(0, 999);

        return $this->render('MaterialLibBundle:Web/Widget:thumb-list.html.twig', array(
            'currentUserId'  => $currentUserId,
            'files'          => $files,
            'createdUsers'   => $createdUsers,
            'paginator'      => $paginator,
            'storageSetting' => $storageSetting,
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

    public function collectAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $data = $request->query->all();

        $collection = $this->getUploadFileService()->collectFile($user['id'], $data['fileId']);
        if (empty($collection)) {
            return $this->createJsonResponse(false);
        }
        return $this->createJsonResponse(true);
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

    public function generateThumbnailAction(Request $request, $globalId)
    {
        $second = $request->query->get('second');
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
