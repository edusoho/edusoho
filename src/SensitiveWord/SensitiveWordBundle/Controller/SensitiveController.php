<?php
namespace SensitiveWord\SensitiveWordBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\AdminBundle\Controller\BaseController;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class SensitiveController extends BaseController
{
    public function indexAction(Request $request)
    {
        $paginator = new Paginator($this->get('request') , $this->getSensitiveService()->searchkeywordsCount() , 50);
        $keywords = $this->getSensitiveService()->searchKeywords($paginator->getOffsetCount() , $paginator->getPerPageCount());


        return $this->render('SensitiveWordBundle:SensitiveAdmin:index.html.twig', array(
            'keywords' => $keywords,
            'paginator' => $paginator
        ));
    }

     public function sensitiveCheckAction(Request $request, $type ='')
    {
        $text = $request->query->get('value');
        if(empty($text)){
            $text = $request->request->get('value');
        }
        $isValidate = $this->getSensitiveService()->sensitiveCheck($text, $type);
        
        if ($isValidate) {
            $response =  $isValidate;
        } else {
            $response = array('success' => true, 'message' => '校验通过');
        }
        return $this->createJsonResponse($response);
    }
    
    public function createAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $keyword = $request->request->get('name');
            $keyword = $this->getSensitiveService()->addKeyword($keyword);
            return $this->redirect($this->generateUrl('admin_keyword'));
        }
        
        return $this->render('SensitiveWordBundle:SensitiveAdmin:keyword-add.html.twig');
    }
    
    public function deleteAction(Request $request, $id)
    {
        $this->getSensitiveService()->deleteKeyword($id);
        return $this->redirect($this->generateUrl('admin_keyword'));
    }
    
    public function banlogsAction(Request $request)
    {
        $conditions = array();
        
        $count = $this->getSensitiveService()->searchBanlogsCount($conditions);
        $paginator = new Paginator($this->get('request') , $count, 50);
        
        $banlogs = $this->getSensitiveService()->searchBanlogs($conditions, array(
            'createdTime',
            'DESC'
        ) , $paginator->getOffsetCount() , $paginator->getPerPageCount());
        
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($banlogs, 'userId'));
        
        return $this->render('SensitiveWordBundle:SensitiveAdmin:banlogs.html.twig', array(
            'banlogs' => $banlogs,
            'users' => $users,
            'paginator' => $paginator,
        ));
    }
    
    protected function getSensitiveService()
    {
        return $this->getServiceKernel()->createService('SensitiveWord:Sensitive.SensitiveService');
    }
}
