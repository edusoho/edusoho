<?php

namespace ApiBundle\Api\Resource\Trade;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Trade\Factory\BaseTrade;
use ApiBundle\Api\Resource\Trade\Factory\TradeFactory;
use Biz\Common\CommonException;
use Biz\OrderFacade\Service\OrderFacadeService;
use Codeages\Biz\Order\Service\OrderService;
use Codeages\Biz\Pay\Service\PayService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Codeages\Biz\Pay\Exception\PayGatewayException;

class Trade extends AbstractResource
{
    public function get(ApiRequest $request, $tradeSn)
    {
        $trade = $this->getPayService()->queryTradeFromPlatform($tradeSn);

        return array(
            'isPaid' => 'paid' === $trade['status'],
            'paidSuccessUrl' => $this->generateUrl('cashier_pay_success', array('trade_sn' => $tradeSn)),
            'paidSuccessUrlH5' => $this->generateUrl('cashier_pay_success_for_h5', array('trade_sn' => $tradeSn)),
        );
    }

    /**
     * gateway 支付网关
     * type 交易类型
     * orderSn 订单号
     * coinAmount 使用多少虚拟币
     * payPassword 支付密码
     *
     * @param \ApiBundle\Api\ApiRequest $request
     *
     * @return array
     */
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();
        if (empty($params['gateway']) || empty($params['type'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        try {
            $this->fillParams($params);

            if (!empty($params['orderSn']) && $order = $this->getOrderService()->getOrderBySn($params['orderSn'])) {
                if ($this->isOrderPaid($order)) {
                    return array(
                        'tradeSn' => $order['trade_sn'],
                        'isPaid' => true,
                        'paidSuccessUrl' => $this->generateUrl('cashier_pay_success', array('trade_sn' => $order['trade_sn'])),
                        'paidSuccessUrlH5' => $this->generateUrl('cashier_pay_success_for_h5', array('trade_sn' => $order['trade_sn'])),
                    );
                } else {
                    $this->getOrderFacadeService()->checkOrderBeforePay($params['orderSn'], $params);
                }
            }
            $tradeIns = $this->getTradeIns($params['gateway']);
            $trade = $tradeIns->create($params);

            if (0 == $trade['cash_amount']) {
                $trade = $this->getPayService()->notifyPaid('coin', array('trade_sn' => $trade['trade_sn']));
            }
        } catch (PayGatewayException $e) {
            throw new BadRequestHttpException($e->getMessage(), $e, ErrorCode::BAD_REQUEST);
        } catch (OrderPayCheckException $payCheckException) {
            throw new BadRequestHttpException($payCheckException->getMessage(), $payCheckException, $payCheckException->getCode());
        }

        return $tradeIns->createResponse($trade);
    }

    private function isOrderPaid($order)
    {
        //如果订单已经支付不去查询第三方
        if ($this->getOrderFacadeService()->isOrderPaid($order['id'])) {
            return true;
        }

        if ($order['trade_sn']) {
            $trade = $this->getPayService()->queryTradeFromPlatform($order['trade_sn']);

            return 'paid' === $trade['status'];
        }

        return false;
    }

    /**
     * @param $gateway
     *
     * @return BaseTrade
     */
    private function getTradeIns($gateway)
    {
        $factory = new TradeFactory();
        $tradeIns = $factory->create($gateway);
        $tradeIns->setContainer($this->container);
        $tradeIns->setBiz($this->biz);

        return $tradeIns;
    }

    private function fillParams(&$params)
    {
        $params['userId'] = $this->getCurrentUser()->getId();
        $params['clientIp'] = $this->getClientIp();
        if (!isset($params['app_pay'])) {
            $params['app_pay'] = isset($params['appPay']) && 'N' == $params['appPay'] ? 'N' : 'Y';
        }
        if (isset($params['payPassword'])) {
            $params['payPassword'] = \XXTEA::decrypt(base64_decode($params['payPassword']), 'EduSoho');
        }

        if (isset($params['unencryptedPayPassword'])) {
            $params['payPassword'] = $params['unencryptedPayPassword'];
        }
    }

    /**
     * @return OrderService
     */
    private function getOrderService()
    {
        return $this->service('Order:OrderService');
    }

    /**
     * @return PayService
     */
    private function getPayService()
    {
        return $this->service('Pay:PayService');
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->service('OrderFacade:OrderFacadeService');
    }
}
