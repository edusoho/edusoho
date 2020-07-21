<?php

namespace ApiBundle\Api\Resource\Order;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\Order\OrderException;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Order extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();

        if (empty($params['targetId'])
            || empty($params['targetType'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $this->filterParams($params);

        try {
            /* @var $product Product */
            $product = $this->getOrderFacadeService()->getOrderProduct($params['targetType'], $params);
            $product->setPickedDeduct($params);

            $this->addCreateDealers($request);

            $order = $this->getOrderFacadeService()->create($product);

            if (!empty($params['isOrderCreate'])) {
                return $order;
            }
            // 优惠卷全额抵扣
            if ($this->getOrderFacadeService()->isOrderPaid($order['id'])) {
                return [
                    'id' => $order['id'],
                    'sn' => $order['sn'],
                ];
            } else {
                $this->handleParams($params, $order);
                $apiRequest = new ApiRequest('/api/trades', 'POST', [], $params);
                $trade = $this->invokeResource($apiRequest);

                return [
                    'id' => $trade['tradeSn'],
                    'sn' => $trade['tradeSn'],
                ];
            }
        } catch (OrderPayCheckException $payCheckException) {
            throw new BadRequestHttpException($payCheckException->getMessage(), $payCheckException, $payCheckException->getCode());
        }
    }

    public function get(ApiRequest $request, $sn)
    {
        $order = $this->getOrderService()->getOrderBySn($sn);
        if (!$order) {
            throw OrderException::NOTFOUND_ORDER();
        }
        $paymentTrade = $this->getPayService()->getTradeByTradeSn($order['trade_sn']);
        $order['platform_sn'] = $paymentTrade['platform_sn'];
        $userId = $this->getCurrentUser()->getId();
        if ($this->getCurrentUser()->isAdmin()) {
            return $order;
        } elseif ($userId == $order['user_id']) {
            return $order;
        }
    }

    public function filterParams(&$params)
    {
        if (isset($params['coinPayAmount'])) {
            $params['coinAmount'] = $params['coinPayAmount'];
            unset($params['coinPayAmount']);
        }

        if (isset($params['payPassword'])) {
            $params['payPassword'] = $this->decrypt($params['payPassword']);
        }

        if (isset($params['unencryptedPayPassword'])) {
            $params['payPassword'] = $params['unencryptedPayPassword'];
        }
    }

    public function handleParams(&$params, $order)
    {
        $params['gateway'] = (!empty($params['payment']) && 'wechat' == $params['payment']) ? 'WechatPay_MWeb' : 'Alipay_LegacyWap';
        $params['type'] = 'purchase';
        $params['app_pay'] = isset($params['appPay']) && 'Y' == $params['appPay'] ? 'Y' : 'N';
        $params['orderSn'] = $order['sn'];
        if (isset($params['payPassword'])) {
            $params['unencryptedPayPassword'] = $params['payPassword'];
        }
        if ('Alipay_LegacyWap' == $params['gateway']) {
            $params['return_url'] = $this->generateUrl('cashier_pay_return_for_app', ['payment' => 'alipay'], UrlGeneratorInterface::ABSOLUTE_URL);
            $params['show_url'] = $this->generateUrl('cashier_pay_return_for_app', ['payment' => 'alipay'], UrlGeneratorInterface::ABSOLUTE_URL);
        }
    }

    private function addCreateDealers($request)
    {
        $serviceNames = ['Distributor:DistributorProductDealerService', 'S2B2C:S2B2CProductDealerService'];

        foreach ($serviceNames as $serviceName) {
            $service = $this->getBiz()->service($serviceName);
            $service->setParams($request->getHttpRequest()->cookies->all());
            $this->getOrderFacadeService()->addDealer($service);
        }
    }

    private function decrypt($payPassword)
    {
        return \XXTEA::decrypt(base64_decode($payPassword), 'EduSoho');
    }

    /**
     * @return OrderService
     */
    protected function getOrderService()
    {
        return $this->getBiz()->service('Order:OrderService');
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->service('OrderFacade:OrderFacadeService');
    }

    /**
     * @return PayService
     */
    protected function getPayService()
    {
        return $this->service('Pay:PayService');
    }
}
