<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Service\Util\CloudClientFactory;
use Symfony\Component\HttpFoundation\Request;

class CloudController extends BaseController
{
    public function billAction(Request $request)
    {
        $factory = new CloudClientFactory();
        $client  = $factory->createClient();

        $result = $client->getBills();
        if (!empty($result['error'])) {
            return $this->createMessageResponse('error', $this->getServiceKernel()->trans('获取账单信息失败，云视频参数配置不正确，或网络通讯失败。, 获取账单信息失败'));
        }

        return $this->render('TopxiaAdminBundle:Cloud:bill.html.twig', array(
            'money' => $result['money'],
            'bills' => $result['bills']
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

    public function accountPersonAction(Request $request)
    {
        return $this->redirectUrl('account_person');
    }

    public function cloudShowAction(Request $request, $type)
    {
        return $this->redirectUrl('edu_cloud_show', array('type' => $type));
    }

    public function liveUpgradeAction(Request $request)
    {
        return $this->redirectUrl('edu_cloud_live_upgrade');
    }

    public function liveRenewAction(Request $request)
    {
        return $this->redirectUrl('edu_cloud_live_renew');
    }

    public function emailListAction(Request $request)
    {
        return $this->redirectUrl('service_email_list');
    }

    public function emailCountAction(Request $request)
    {
        return $this->redirectUrl('service_email_count');
    }

    public function listCouponAction(Request $request)
    {
        return $this->redirectUrl('list_coupon');
    }

    public function serviceOverviewAction(Request $request, $type)
    {
        $url = 'service_'.$type.'_overview';
        return $this->redirectUrl($url);
    }

    public function detailAction(Request $request)
    {
        return $this->redirectUrl('bill_list');
    }

    public function smsAccountAction(Request $request)
    {
        return $this->redirectUrl('service_sms_count');
    }

    public function emailAccountAction(Request $request)
    {
        return $this->redirectUrl('service_email_count');
    }

    public function smsSignAction(Request $request)
    {
        return $this->redirectUrl('service_sms_sign');
    }

    public function videoAccountAction(Request $request)
    {
        return $this->redirectUrl('bill_video_detail');
    }

    public function buyAction(Request $request, $type)
    {
        $params = array('type' => $type);
        return $this->redirectUrl('edu_cloud_buy_custom', $params);
    }

    public function videoDetailAction()
    {
        return $this->redirectUrl('service_storage_chart');
    }

    public function emailBuyAction(Request $request, $type)
    {
        $params = array('type' => $type);
        return $this->redirectUrl('edu_cloud_buy_custom', $params);
    }

    public function tlpAction(Request $request)
    {
        $params = array('type' => 'tlp');
        return $this->redirectUrl('edu_cloud_show', $params);
    }

    public function videoAction(Request $request)
    {
        $params = array('type' => 'video');
        return $this->redirectUrl('edu_cloud_show', $params);
    }

    public function docAction(Request $request)
    {
        $params = array('type' => 'doc');
        return $this->redirectUrl('edu_cloud_show', $params);
    }

    public function searchAction(Request $request)
    {
        $params = array('type' => 'search');
        return $this->redirectUrl('edu_cloud_show', $params);
    }

    public function liveAction(Request $request)
    {
        $params = array('type' => 'live');
        return $this->redirectUrl('edu_cloud_show', $params);
    }

    public function videoUpgradeAction(Request $request)
    {
        return $this->redirectUrl('edu_cloud_video_upgrade');
    }

    public function videoRenewAction(Request $request)
    {
        return $this->redirectUrl('edu_cloud_video_renew');
    }

    public function searchDetailAction(Request $request)
    {
        return $this->redirectUrl('service_search_overview');
    }

    public function smsDetailAction(Request $request)
    {
        return $this->redirectUrl('service_sms_list_detail');
    }

    public function smsStatisticsAction(Request $request)
    {
        return $this->redirectUrl('service_sms_count');
    }

    public function smsSettingAction(Request $request)
    {
        return $this->redirectUrl('service_sms_setting');
    }

    public function liveMoreAction(Request $request)
    {
        return $this->redirectUrl('service_live_count');
    }

    protected function redirectUrl($routingName, $params = array())
    {
        $url = $this->getAppService()->getTokenLoginUrl($routingName, $params);
        return $this->redirect($url);
    }

    protected function createAppClient()
    {
        if (!isset($this->client)) {
            $cloud     = $this->getSettingService()->get('storage', array());
            $developer = $this->getSettingService()->get('developer', array());

            $options = array(
                'accessKey' => empty($cloud['cloud_access_key']) ? null : $cloud['cloud_access_key'],
                'secretKey' => empty($cloud['cloud_secret_key']) ? null : $cloud['cloud_secret_key'],
                'apiUrl'    => empty($developer['app_api_url']) ? null : $developer['app_api_url'],
                'debug'     => empty($developer['debug']) ? false : true
            );

            $this->client = new EduSohoAppClient($options);
        }

        return $this->client;
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
