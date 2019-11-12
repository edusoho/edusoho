<?php

namespace AppBundle\Controller\AdminV2\CloudCenter;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\Service\AppService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class CloudController extends BaseController
{
    public function buyAction(Request $request, $type)
    {
        $params = array('type' => $type);

        return $this->redirectUrl('edu_cloud_buy', $params);
    }

    public function accessWechatAction(Request $request)
    {
        return $this->redirectUrl('product_marketing_detail', array('type' => 'microprogram'));
    }

    public function accessAction(Request $request)
    {
        return $this->redirectUrl('edu_cloud');
    }

    public function smsAccountAction(Request $request)
    {
        return $this->redirectUrl('service_sms_count');
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

    public function cloudShowAction(Request $request, $type)
    {
        return $this->redirectUrl('edu_cloud_show', array('type' => $type));
    }

    public function videoAction(Request $request)
    {
        $params = array('type' => 'video');

        return $this->redirectUrl('edu_cloud_show', $params);
    }

    public function videoRenewAction(Request $request)
    {
        return $this->redirectUrl('edu_cloud_video_renew');
    }

    public function videoDetailAction()
    {
        return $this->redirectUrl('service_storage_chart');
    }

    public function videoUpgradeAction(Request $request)
    {
        return $this->redirectUrl('edu_cloud_video_upgrade');
    }

    public function videoAccountAction(Request $request)
    {
        return $this->redirectUrl('bill_video_detail');
    }

    public function liveMoreAction(Request $request)
    {
        return $this->redirectUrl('service_live_count');
    }

    public function liveUpgradeAction(Request $request)
    {
        return $this->redirectUrl('edu_cloud_live_upgrade');
    }

    public function liveRenewAction(Request $request)
    {
        return $this->redirectUrl('edu_cloud_live_renew');
    }

    public function rechargeAction(Request $request)
    {
        return $this->redirectUrl('order_recharge');
    }

    public function accountPersonAction(Request $request)
    {
        return $this->redirectUrl('account_person');
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

    public function smsSignAction(Request $request)
    {
        return $this->redirectUrl('service_sms_sign');
    }

    public function emailBuyAction(Request $request, $type)
    {
        $params = array('type' => $type);

        return $this->redirectUrl('edu_cloud_buy_custom', $params);
    }

    public function emailListAction(Request $request)
    {
        return $this->redirectUrl('service_email_list');
    }

    public function liveAction(Request $request)
    {
        $params = array('type' => 'live');

        return $this->redirectUrl('edu_cloud_show', $params);
    }

    public function tlpAction(Request $request)
    {
        $params = array('type' => 'tlp');

        return $this->redirectUrl('edu_cloud_show', $params);
    }

    public function searchAction(Request $request)
    {
        $params = array('type' => 'search');

        return $this->redirectUrl('edu_cloud_show', $params);
    }

    public function searchDetailAction(Request $request)
    {
        return $this->redirectUrl('service_search_overview');
    }

    public function detailAction(Request $request)
    {
        return $this->redirectUrl('bill_list');
    }

    public function emailCountAction(Request $request)
    {
        return $this->redirectUrl('service_email_count');
    }

    public function docAction(Request $request)
    {
        $params = array('type' => 'doc');

        return $this->redirectUrl('edu_cloud_show', $params);
    }

    protected function redirectUrl($routingName, $params = array())
    {
        $url = $this->getAppService()->getTokenLoginUrl($routingName, $params);

        return $this->redirect($url);
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->createService('CloudPlatform:AppService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
