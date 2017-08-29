<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Controller\BaseController;
use Biz\Cash\Service\CashService;
use Biz\Order\Service\OrderService;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Pay\Service\AccountService;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Omnipay\WechatPay\Helper;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\MathToolkit;

class CoinController extends BaseController
{
    public function showAction()
    {
        $coinSetting = $this->getSettingService()->get('coin');

        return $this->render('cashier/coin/show.html.twig', array(
            'coinSetting' => $coinSetting,
            'balance' => array('amount' => 100),
            'account' => array('password' => 'xx')
        ));
    }

    /**
     * @return AccountService
     */
    private function getAccountService()
    {
        return $this->createService('Pay:AccountService');
    }

    /**
     * @return SettingService
     */
    private function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return PayService
     */
    private function getPayService()
    {
        return $this->createService('Pay:PayService');
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->createService('Order:OrderService');
    }
}
