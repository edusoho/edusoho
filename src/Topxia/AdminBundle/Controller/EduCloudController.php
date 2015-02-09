<?php

namespace Topxia\AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\FileToolkit;
use Topxia\Component\OAuthClient\OAuthClientFactory;
use Topxia\Service\Util\LiveClientFactory;
use Topxia\Service\Util\CloudClientFactory;

class EduCloudController extends BaseController
{
    private $cloudOptions = null;
    private $cloudApi = null;
    private $debug = true;

    public function indexAction(Request $request)
    {
        $loginToken = $this->getAppService()->getLoginToken();
        $hasAccount = isset($loginToken["token"]);

        $money = '--';
        try{
            $result = $this->getAccounts();
        }catch(\RuntimeException $e){
            return $this->render('TopxiaAdminBundle:EduCloud:api-error.html.twig', array());
        }
        
        if (isset($result['cash'])){
            $money = $result['cash'];
        }

        $smsStatus = array();
        try{
            $result = $this->lookForStatus();
        }catch(\RuntimeException $e){
            return $this->render('TopxiaAdminBundle:EduCloud:api-error.html.twig', array());
        }
        if (isset($result['apply']) && isset($result['apply']['status'])){
            $smsStatus['status'] = $result['apply']['status'];
            $smsStatus['message'] = $result['apply']['message'];
        }else if (isset($result['error'])) {
            $smsStatus['status'] = 'error';
            $smsStatus['message'] = $result['error'];
        }

        if($this->debug){
            $hasAccount = true;
            $loginToken["token"] = '8888';
        }

        return $this->render('TopxiaAdminBundle:EduCloud:edu-cloud.html.twig', array(
            'money' => $money,
            'hasAccount' => $hasAccount,
            'token' => $hasAccount ? $loginToken["token"] : '',
            'smsStatus' => $smsStatus,
        ));
    }

    public function smsAction(Request $request)
    {
        $this->handleSmsSetting($request);
        $smsStatus = array();

        try{
            $result = $this->lookForStatus();
        }catch(\RuntimeException $e){
            return $this->render('TopxiaAdminBundle:EduCloud:api-error.html.twig', array());
        }

        if (isset($result['apply']) && isset($result['apply']['status'])){
            $smsStatus['status'] = $result['apply']['status'];
        }else if (isset($result['error'])) {
            $smsStatus['status'] = 'error';
            $smsStatus['message'] = $result['error'];
        }

        return $this->render('TopxiaAdminBundle:EduCloud:sms.html.twig', array(
            'smsStatus' => $smsStatus,
        ));
    }

    public function smsUsageAction(Request $request)
    {
        //8888888888
    }

    public function applyForSmsAction(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $result = null;
            $dataUserPosted = $request->request->all();

            if (
                isset($dataUserPosted['name']) 
                && ($this->calStrlen($dataUserPosted['name'])>=2) 
                && ($this->calStrlen($dataUserPosted['name'])<=8)
            ){
                $result = $this->applyForSms($dataUserPosted['name']);                
                if (isset($result['status']) && ($result['status'] == 'ok')) {
                    $this->setCloudSmsKey('sms_school_name', $dataUserPosted['name']);
                    return $this->createJsonResponse(array('ACK' => 'ok'));
                }
            }

            return $this->createJsonResponse(array(
                'ACK' => 'failed', 
                'message' => $result['error'].'|'.($this->calStrlen($dataUserPosted['name']))
            ));
        }
        return $this->render('TopxiaAdminBundle:EduCloud:apply-sms-form.html.twig', array());
    }

    public function smsSwitchAction(Request $request, $open)
    {
        //8888888888
    }


    public function smsCaptchaAction(Request $request)
    {
        //8888888888
    }    


    private function handleSmsSetting(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            $dataUserPosted =  $request->request->all();
            $this->setCloudSmsKey('sms_enabled', $dataUserPosted['sms_enabled']);
            if (isset($dataUserPosted['sms_registration'])&&($dataUserPosted['sms_registration']=='on')) {
                $this->setCloudSmsKey('sms_registration', 'on');
            }else{
                $this->setCloudSmsKey('sms_registration', 'off');
            }
            if (isset($dataUserPosted['sms_find_password'])&&($dataUserPosted['sms_find_password']=='on')) {
                $this->setCloudSmsKey('sms_find_password', 'on');
            }else{
                $this->setCloudSmsKey('sms_find_password', 'off');
            }
            if (isset($dataUserPosted['sms_user_pay'])&&($dataUserPosted['sms_user_pay']=='on')) {
                $this->setCloudSmsKey('sms_user_pay', 'on');
            }else{
                $this->setCloudSmsKey('sms_user_pay', 'off');
            }
            if (isset($dataUserPosted['sms_find_pay_password'])&&($dataUserPosted['sms_find_pay_password']=='on')) {
                $this->setCloudSmsKey('sms_find_pay_password', 'on');
            }else{
                $this->setCloudSmsKey('sms_find_pay_password', 'off');
            }   

            if ('1' == $dataUserPosted['sms_enabled']){
                $this->setFlashMessage('success', '短信功能开启成功，每条短信0.07元。');   
            }else{
                $this->setFlashMessage('success', '设置成功。');  
            }
        }
    }

    private function calStrlen($str)
    {
        return (strlen($str) + mb_strlen($str,'UTF8')) / 2; 
    }  

    private function getCloudOptions()
    {        
        if (empty($this->cloudOptions)) {
            $settings = $this->getServiceKernel()->createService('System.SettingService')->get('storage', array());
            $this->cloudOptions = array(
                'accessKey' => empty($settings['cloud_access_key']) ? '' : $settings['cloud_access_key'],
                'secretKey' => empty($settings['cloud_secret_key']) ? '' : $settings['cloud_secret_key'],
                'apiUrl' => empty($settings['cloud_api_server']) ? '' : $settings['cloud_api_server'],
            );
        }        
        return $this->cloudOptions;
    }
      
    private function setCloudSmsKey($key, $val)
    {        
        $setting = $this->getSettingService()->get('cloud_sms', array());
        $setting[$key] = $val;
        $this->getSettingService()->set('cloud_sms', $setting);
    }

    private function getCloudSmsKey($key)
    {        
        $setting = $this->getSettingService()->get('cloud_sms', array());
        return $setting[$key];
    } 

    private function getCloudApi()
    {        
        if (empty($this->cloudApi)) {
            $this->cloudApi = $this->createAPIClient();
        }        
        return $this->cloudApi;
    }

    private function getAccounts()
    {
        return $this->getEduCloudService()->getAccounts();
    }

    private function applyForSms($name = 'smsHead')
    {
        return $this->getEduCloudService()->applyForSms($name);
    }    

    private function lookForStatus()
    {
        return $this->getEduCloudService()->lookForStatus();
    }

    private function sendSms($to, $captcha, $category = 'captcha')
    {
        return $this->getEduCloudService()->sendSms($to, $captcha, $category);
    }

    private function verifyKeys()
    {
        return $this->getEduCloudService()->verifyKeys();
    }   

    protected function getEduCloudService()
    {
        return $this->getServiceKernel()->createService('EduCloud.EduCloudService');   
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    } 

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }   
}