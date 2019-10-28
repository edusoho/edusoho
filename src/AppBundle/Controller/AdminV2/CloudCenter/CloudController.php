<?php

namespace AppBundle\Controller\AdminV2\CloudCenter;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\CloudPlatform\Service\AppService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;

class CloudController extends BaseController
{
    public function accessWechatAction(Request $request)
    {
        return $this->redirectUrl('product_marketing_detail', array('type' => 'microprogram'));
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

    public function listCouponAction(Request $request)
    {
        return $this->redirectUrl('list_coupon');
    }

    public function serviceOverviewAction(Request $request, $type)
    {
        $url = 'service_'.$type.'_overview';

        return $this->redirectUrl($url);
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
