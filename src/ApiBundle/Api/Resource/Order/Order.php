<?php

namespace ApiBundle\Api\Resource\Order;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\OrderFacade\Exception\OrderPayCheckException;
use Biz\OrderFacade\Product\Product;
use Biz\OrderFacade\Service\OrderFacadeService;
use Codeages\Biz\Framework\Pay\Service\PayService;
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
            $params['clientIp'] = $this->getClientIp();
            $params['payment'] = 'alipay';
            $trade = $this->getOrderFacadeService()->payingOrder($order['sn'], $params);
            $trade['pay_type'] = 'Wap';
            $trade['notify_url'] = $this->generateUrl('cashier_pay_notify', array('payment' => 'alipay'), true);
            $trade['return_url'] = $this->generateUrl('cashier_pay_return_for_app', array('payment' => 'alipay'), true);
            $result = $this->getPayService()->createTrade($trade);

            return array(
                'id' => $result['trade_sn'],
                'sn' => $result['trade_sn']
            );
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

    private function decrypt($payPassword)
    {
        return \XXTEA::decrypt(base64_decode($payPassword), 'EduSoho');
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