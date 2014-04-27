<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Component\Payment\Payment;
use Symfony\Component\HttpFoundation\Response;

class AdController extends BaseController
{
    
    public function getAdAction(Request $request)
    {

        $targetUrl=  $request->request->get('targetUrl');
               
         
        return $this->createJsonResponse(array('run'=>false,'showUrl'=>'/dddd'));
    }


    private function getAdService()
    {
        return $this->getServiceKernel()->createService('Ad.SettingService');
    }

   

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

   

}