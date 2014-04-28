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
           
        }else{

             $adSetting['run']=false;

             $showUrl = $adSetting['showUrl'];

             if(substr($showUrl,0,1) == '/' or strtolower(substr($showUrl,0,4)) == 'http'){

             }else{

                 $block = $this->getBlockService()->getBlockByCode($showUrl);

                 $adSetting['showUrl']=$block['content'];

             }

            if($adSetting['scope']==1){//仅游客
                
                $currentUser = $this->getCurrentUser();

                if (empty($currentUser['id'])) {
                    $adSetting['run']=true;
                }


            }else if($adSetting['scope']==2){//仅注册用户
                $currentUser = $this->getCurrentUser();

                if ($currentUser['id']) {
                    $adSetting['run']=true;
                }

            }else{

                 $adSetting['run']=true;

            }


           
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


    protected function getBlockService()
    {
        return $this->getServiceKernel()->createService('Content.BlockService');
    }

   

}