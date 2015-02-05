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
    public function indexAction(Request $request)
    {
        //8888888888

        return $this->render('TopxiaAdminBundle:System:edu-cloud.html.twig', array());
    }

    public function smsAction(Request $request)
    {
        //8888888888
        // depricated
        // $factory = new CloudClientFactory();
        // $client = $factory->createClient();
        // var_dump($client->getBucket());
        // var_dump($client->getBills($client->getBucket()));
        // exit;

    // return $this->render('TopxiaAdminBundle:Cloud:bill.html.twig', array(
    //             'money' => $result['money'],
    //             'bills' => $result['bills'],
    //             'token' => $loginToken["token"]
    //         ));

      // <div class="well well-sm">
      //   账户余额：<strong class="text-success">{{ money }}元</strong>&nbsp;&nbsp;&nbsp;&nbsp;
      //   <a href="http://open.edusoho.com/token_login?token={{token}}&goto=order_recharge" target="_blank" class="btn btn-primary">充值</a>
      //   <a href="http://open.edusoho.com/article/1#recharge" style="float:right;" target="_blank" class="btn btn-link">充值帮助</a>
      // </div>


        // $result = $client->getBills($client->getBucket());


        $settings = $this->getServiceKernel()->createService('System.SettingService')->get('storage', array());
        $options = array(
            'accessKey' => empty($settings['cloud_access_key']) ? '' : $settings['cloud_access_key'],
            'secretKey' => empty($settings['cloud_secret_key']) ? '' : $settings['cloud_secret_key'],
            'apiUrl' => empty($settings['cloud_api_server']) ? '' : $settings['cloud_api_server'],
        );

        $api = $this->createAPIClient();

        // $result = $api->get('/me');

        //get bills
        // $result = $api->get(sprintf('/accounts'));
        $result = $api->get('/accounts');

        //verify accesss key
        // $result = $api->post(sprintf('/keys/%s/verification', $options['accessKey']));

        //apply for sms service
        // $result = $api->post(
        //     sprintf('/sms/%s/apply', $options['accessKey']), 
        //     $params=array('name'=>'Eschol8' ) );

        //look for apply status
        // $result = $api->post(
        //     sprintf('/sms/%s/applyResult', $options['accessKey']) );

        //send a sms
        // $result = $api->post(
        //     sprintf('/sms/%s/sendVerify', $options['accessKey']),
        //     $params=array('mobile'=>'13758129341', 'verify'=>'D35H72', 'category' => '不必须') );
        var_dump($result);
        exit;
        return $this->render('TopxiaAdminBundle:System:sms.html.twig', array());
    }

    public function applyForSmsAction(Request $request)
    {
        //8888888888
    }

    public function smsSwitchAction(Request $request, $open)
    {
        //8888888888
    }

    public function smsUsageAction(Request $request)
    {
        //8888888888
    }

}