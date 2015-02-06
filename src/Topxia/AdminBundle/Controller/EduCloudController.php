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
        $result = $this->getAccounts();
        if (isset($result['cash'])){
            $money = $result['cash'];
        }

        $smsStatus = array();
        $result = $this->lookForStatus();
        if (isset($result['apply']) && isset($result['apply']['status'])){
            $smsStatus['status'] = $result['apply']['status'];
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
        //8888888888
        // $result = $this->lookForStatus();
        // $result = $this->sendSms('13758129341', '3572');
        $result = $this->applyForSms('Sch'.rand(100,999));
        // $result = $this->lookForStatus();
        // $result = $this->verifyKeys();
        // $result = $this->lookForStatus();
        var_dump($result);
        exit;
        return $this->render('TopxiaAdminBundle:EduCloud:sms.html.twig', array());
    }

    public function smsUsageAction(Request $request)
    {
        //8888888888
    }

    public function applyForSmsAction(Request $request)
    {
        // $verification = $this->verifyKeys();
        // if (isset($verification['status']) && ($verification['status'] == 'ok')) {
        //     return $this->createJsonResponse(array('ACK' => 'failed'));
        // }

        if ($request->getMethod() == 'POST') {
            $result = null;
            $dataUserPosted = $request->request->all();
            error_log(serialize($dataUserPosted),3,'/var/tmp/wangchao');

            if (
                isset($dataUserPosted['name']) 
                && ($this->calStrlen($dataUserPosted['name'])>=2) 
                && ($this->calStrlen($dataUserPosted['name'])<=8)
            ){
                $result = $this->applyForSms($dataUserPosted['name']);
                if (isset($result['status']) && ($result['status'] == 'ok')) {
                    return $this->createJsonResponse(array('ACK' => 'ok'));
                }
            }
            error_log(serialize($result),3,'/var/tmp/wangchao');
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
}