<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class MaterialLibController extends BaseController
{
    public function indexAction (Request $request, $type='all', $viewMode='thumb')
    {
        $user = $this->getCurrentUser();
        
        $keyWord = $request->query->get('keyword') ? : "";
        $sortBy = $request->query->get('sortBy') ? : "latestUpdated";
        
        $param = array();
        
        if($type <> 'all'){
            $param['type'] = $type;
        }
        
        if($keyWord <> ''){
            $param['filename'] = $keyWord;
        }

        $paginator = new Paginator(
            $request,
            $this->getUploadFileService()->searchFileCount($param)
        );

        $materialResults = $this->getUploadFileService()->searchFiles(
            $param,
            $sortBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        if($viewMode == 'thumb'){
            $resultPage = 'TopxiaWebBundle:MaterialLib:material-thumb-view.html.twig';
        }else{
            $resultPage = 'TopxiaWebBundle:MaterialLib:material-list-view.html.twig';
        }
        
        return $this->render($resultPage, array(
            'type' => $type,
            'materialResults' => $materialResults,
            'paginator' => $paginator
        ));
    }
    
    public function deleteAction(Request $request, $id)
    {
        $this->getUploadFileService()->deleteFile($id);
    
        return $this->createJsonResponse(true);
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    private function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }
}