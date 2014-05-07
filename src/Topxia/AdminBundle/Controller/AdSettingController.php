<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\StringToolkit;
use Topxia\Component\Payment\Payment;
use Symfony\Component\HttpFoundation\Response;

class AdSettingController extends BaseController
{
    
    public function listAction(Request $request)
    {

        $conditions = $request->query->all();

        $count = $this->getAdSettingService()->searchSettingCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);

        $settings = $this->getAdSettingService()->searchSettings($conditions,'latest', $paginator->getOffsetCount(),  $paginator->getPerPageCount());


        return $this->render('TopxiaAdminBundle:Sale:ad-setting-list.html.twig', array(
            'conditions' => $conditions,
            'settings' => $settings ,
            'paginator' => $paginator
        ));
    }


    public function createAction(Request $request)
    {

        if('POST' == $request->getMethod()){

            $id=  $request->request->get('id');

            $setting = $this->getAdSettingService()->getSetting($id);

            if(empty($setting)){

                $setting = $request->request->all();

                $this->getAdSettingService()->createSetting($setting);


            }else{

                $setting = $request->request->all();

                $this->getAdSettingService()->updateSetting($id,$setting);


            }
            
            return $this->redirect($this->generateUrl('admin_ad_setting')); 
        }
      
        $setting = array(
            'id'=>0,
            'targetUrl'=>'',
            'showUrl'=>'',
            'showMode'=>0,
            'showWhen'=>0,
            'showWait'=>0,
            'scope'=>0,
            'name'=>'',
        );

        return $this->render('TopxiaAdminBundle:Sale:ad-setting-create-modal.html.twig',array('setting' => $setting));
   

    }

    public function editAction(Request $request,$id)
    {

       $setting = $this->getAdSettingService()->getSetting($id);

       if(empty($setting)){

            $setting = array(
                'id'=>0,
                'targetUrl'=>'',
                'showUrl'=>'',
                'showMode'=>0,
                'showWhen'=>0,
                'showWait'=>0,
                'scope'=>0,
                'name'=>'',
            );

        }      

        return $this->render('TopxiaAdminBundle:Sale:ad-setting-create-modal.html.twig',array('setting' => $setting));
   

    }




    public function targetUrlCheckAction(Request $request){
        
        $targetUrl=  $request->request->get('targetUrl');

        $adSetting = $this->getAdSettingService()->findSettingByTargetUrl($targetUrl);
        
        if (empty($adSetting)) {

             $response = array('success' => true, 'message' => "{$targetUrl}未设置，现在可以设置");
            return $this->createJsonResponse($response);
         
        }else{

            $response = array('success' => false, 'message' => "{$targetUrl}已设置，请重新输入");
            return $this->createJsonResponse($response);

        }


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