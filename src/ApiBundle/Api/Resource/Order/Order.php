<?php

namespace ApiBundle\Api\Resource\Order;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class Order extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $params = $request->request->all();

        if (empty($params['targetId'])
            || empty($params['targetType'])) {
            throw new BadRequestHttpException('Params missing', null, ErrorCode::INVALID_ARGUMENT);
        }

        $this->filterParams($params);

        try {
            /* @var $product Product */
            $product = $this->getOrderFacadeService()->getOrderProduct($params['targetType'], $params);
            $product->setPickedDeduct($params);
            $order = $this->getOrderFacadeService()->create($product);

            // 优惠卷全额抵扣
            if ($this->getOrderFacadeService()->isOrderPaid($order['id'])) {
                return array(
                    'id' => $order['id'],
                    'sn' => $order['sn'],
                );
            } else {
                $this->handleParams($params, $order);
                $apiRequest = new ApiRequest('/api/trades', 'POST', array(), $params);
                $trade = $this->invokeResource($apiRequest);

                return array(
                    'id' => $trade['tradeSn'],
                    'sn' => $trade['tradeSn'],
                );
            }
        } catch (OrderPayCheckException $payCheckException) {
            throw new BadRequestHttpException($payCheckException->getMessage(), $payCheckException, $payCheckException->getCode());
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
        $params['gateway'] = (!empty($params['payment']) && $params['payment'] == 'wechat') ? 'WechatPay_MWeb' : 'Alipay_LegacyWap';
        $params['type'] = 'purchase';
        $params['app_pay'] = isset($params['appPay']) && 'Y' == $params['appPay'] ? 'Y' : 'N';
        $params['orderSn'] = $order['sn'];
        if ($params['gateway'] == 'Alipay_LegacyWap') {
            $params['return_url'] = $this->generateUrl('cashier_pay_return_for_app', array('payment' => 'alipay'), true);
            $params['show_url'] = $this->generateUrl('cashier_pay_return_for_app', array('payment' => 'alipay'), true);
        }
    }

    private function decrypt($payPassword)
    {
        return \XXTEA::decrypt(base64_decode($payPassword), 'EduSoho');
    }

    /**
     * @return OrderFacadeService
     */
    private function getOrderFacadeService()
    {
        return $this->service('OrderFacade:OrderFacadeService');
    }
}
