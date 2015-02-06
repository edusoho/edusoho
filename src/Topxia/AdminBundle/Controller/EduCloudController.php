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

    public function indexAction(Request $request)
    {
        //8888888888

        return $this->render('TopxiaAdminBundle:System:edu-cloud.html.twig', array());
    }

    public function smsAction(Request $request)
    {
        //8888888888
        $result = $this->lookForStatus();
        var_dump($result);
        exit;
        return $this->render('TopxiaAdminBundle:System:sms.html.twig', array());
    }

    public function smsUsageAction(Request $request)
    {
        //8888888888
    }

    public function applyForSmsAction(Request $request)
    {
        //8888888888
    }

    public function smsSwitchAction(Request $request, $open)
    {
        //8888888888
    }


    public function smsCaptchaAction(Request $request)
    {
        //8888888888
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
        $api = $this->getCloudApi();
        $options = $this->getCloudOptions();
        return $api->get('/accounts');
    }

    private function applyForSms($name = 'smsHead')
    {
        $api = $this->getCloudApi();
        $options = $this->getCloudOptions();
        
        $result = $api->post(
            sprintf('/sms/%s/apply', $options['accessKey']), 
            $params = array('name' => $name)
        );

        return $result;
    }    

    private function lookForStatus()
    {
        $api = $this->getCloudApi();
        $options = $this->getCloudOptions();
        $result = $api->post(
            sprintf('/sms/%s/applyResult', $options['accessKey'])
        );
        return $result;
    }

    private function sendSms($to, $captcha, $category = 'captcha')
    {
        $api = $this->getCloudApi();
        $options = $this->getCloudOptions();
        $result = $api->post(
            sprintf('/sms/%s/sendVerify', $options['accessKey']),
            $params=array('mobile' => $to, 'verify'=> $captcha, 'category' => $category)
        );
        return $result;
    }    
}