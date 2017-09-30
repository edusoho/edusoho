<?php

namespace ApiBundle\Api\Resource\Cashier;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Cashier\Trade\TradeFactory;
use Biz\Cashier\Service\CashierService;
use Biz\OrderFacade\Service\OrderFacadeService;
use Codeages\Biz\Framework\Pay\Service\PayService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class CashierTrade extends AbstractResource
{
    public function get(ApiRequest $request, $tradeSn)
    {
        return $this->getPayService()->getTradeByTradeSn($tradeSn);
    }

    public function add(ApiRequest $request)
    {
        $params = $request->request->all();

        if (empty($params['gateway'])
            || empty($params['type'])) {
            throw new BadRequestHttpException('Params missing', null, ErrorCode::INVALID_ARGUMENT);
        }

        $this->fillParams($params);

        if (!empty($params['orderSn'])) {
            $this->getOrderFacadeService()->checkOrderBeforePay($params['orderSn'], $params);
        }

        $tradeIns = $this->getTradeIns($params['gateway']);
        $trade = $tradeIns->create($params);

        if ($trade['cash_amount'] == 0) {
            $trade = $this->getPayService()->notifyPaid('coin', array('trade_sn' => $trade['trade_sn']));
        }

        return $trade;
    }

    /**
     * @param $gateway
     * @return BaseTrade
     */
    private function getTradeIns($gateway)
    {
        $factory = new TradeFactory();
        $tradeIns = $factory->create($gateway);
        $tradeIns->setRouter($this->container->get('router'));
        return $tradeIns;
    }

    private function fillParams($params)
    {
        $params['userId'] = $this->getCurrentUser()->getId();
        $params['clientIp'] = $this->getClientIp();
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

    /**
     * @return CashierService
     */
    private function getCashierService()
    {
        return $this->service('Cashier:CashierService');
    }
}