<?php

namespace ApiBundle\Api\Resource\Order;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Exception\ErrorCode;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Order\Service\OrderFacadeService;
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

        if (isset($params['payPassword'])) {
            $params['payPassword'] = $this->decrypt($params['payPassword']);
        }

        list($order) = $this->getOrderFacadeService()->createOrder($params['targetType'], $params['targetId'], $params);
        return $order;
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
        return $this->service('Order:OrderFacadeService');
    }
}