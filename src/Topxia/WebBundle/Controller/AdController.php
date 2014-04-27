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

        $adSetting = $this->getAdSettingService()->findSettingByTargetUrl($targetUrl);

        if(empty($adSetting)){
            $adSetting['run']=false;
            $adSetting['showUrl']='/404';
        }else{
            $adSetting['run']=true;
        }

        return $this->createJsonResponse($adSetting);
    }


    private function getAdSettingService()
    {
        return $this->getServiceKernel()->createService('Ad.SettingService');
    }

   

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

   

}