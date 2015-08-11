<?php
namespace Topxia\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Topxia\Service\Util\CloudClientFactory;

class CloudController extends BaseController
{
    public function billAction(Request $request)
    {

        $factory = new CloudClientFactory();
        $client = $factory->createClient();

        $result = $client->getBills($client->getBucket());

        if (!empty($result['error'])) {
            return $this->createMessageResponse('error', '获取账单信息失败，云视频参数配置不正确，或网络通讯失败。',  '获取账单信息失败');
        }


        return $this->render('TopxiaAdminBundle:Cloud:bill.html.twig', array(
            'money' => $result['money'],
            'bills' => $result['bills'],
        ));

    }
    public function accessAction(Request $request)
    {
        return $this->redirectUrl('edu_cloud');
    }

    public function rechargeAction(Request $request)
    {
        return $this->redirectUrl('order_recharge');
    }

    public function detailAction(Request $request)
    {
        return $this->redirectUrl('bill_list');
    }

    public function smsAccountAction(Request $request)
    {
        return $this->redirectUrl('service_sms_accout');
    }

    public function buyAction(Request $request,$type)
    {
        $params = array( 'type' => $type );
        return $this->redirectUrl('edu_cloud_buy', $params);
    }

    public function tlpAction(Request $request)
    {
        $params = array( 'type' => 'tlp' );
        return $this->redirectUrl('edu_cloud_show', $params);
    }

    public function videoAction(Request $request)
    {
        $params = array( 'type' => 'video' );
        return $this->redirectUrl('edu_cloud_show', $params);
    }

    public function docAction(Request $request)
    {
        $params = array( 'type' => 'doc' );
        return $this->redirectUrl('edu_cloud_show', $params);
    }

    public function liveAction(Request $request)
    {
        $params = array( 'type' => 'live' );
        return $this->redirectUrl('edu_cloud_show', $params);
    }

    public function videoUpgradeAction(Request $request)
    {
        return $this->redirectUrl('edu_cloud_video_upgrade');
    }

    protected function redirectUrl($routingName, $params = array())
    {
        $loginToken = $this->getAppService()->getLoginToken();
        $url = 'http://open.edusoho.com/token_login?token='.$loginToken["token"].'&goto='.$routingName;
        if(!empty($params)){
            $url .= '&param='.urldecode(json_encode($params));
        }
        return $this->redirect($url);
    }
    
    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }
}