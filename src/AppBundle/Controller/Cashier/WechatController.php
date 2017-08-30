<?php

namespace AppBundle\Controller\Cashier;

use AppBundle\Controller\BaseController;
use Biz\Order\Service\OrderService;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Omnipay\WechatPay\Helper;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\MathToolkit;

class WechatController extends BaseController
{
    public function payAction($trade)
    {
        $trade['pay_type'] = 'Native';
        $trade['notify_url'] = $this->generateUrl('cashier_wechat_notify');

        $result = $this->getPayService()->createTrade($trade);

        if ($result['platform_created_result']['return_code'] == 'SUCCESS') {
            $result = MathToolkit::multiply(
                $result,
                array('amount'),
                0.01
            );

            return $this->render(
                'cashier/wechat/qrcode.html.twig', array(
                'trade' => $result,
                'qrcodeUrl' => $result['platform_created_result']['code_url'],
            ));
        }

        throw new \RuntimeException($result['platform_created_result']['return_msg']);
    }

    public function rollAction(Request $request)
    {
        $tradeSn = $request->query->get('tradeSn');
        $trade = $this->getPayService()->queryTradeFromPlatform($tradeSn);

        if ($trade['trade_state'] === 'SUCCESS') {
            $this->getPayService()->notifyPaid('wechat', Helper::array2xml($trade));

            return $this->createJsonResponse(true);
        } else {
            return $this->createJsonResponse(false);
        }
    }

    public function notifyAction(Request $request, $payment)
    {
        $this->getPayService()->notifyPaid($payment, $request->getContent());

        return $this->createJsonResponse(1);
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
