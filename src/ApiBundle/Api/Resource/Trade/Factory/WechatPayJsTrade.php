<?php

namespace ApiBundle\Api\Resource\Trade\Factory;

use Biz\User\Service\UserService;

class WechatPayJsTrade extends BaseTrade
{
    protected $payment = 'wechat';

    protected $platformType = 'Js';

    public function getCustomFields($params)
    {
        return array(
            'open_id' => $params['openid'],
        );
    }

    public function getCustomResponse($trade)
    {
        if ('purchase' == $trade['type']) {
            $order = $this->getOrderService()->getOrderBySn($trade['order_sn']);

            $items = $this->getOrderService()->findOrderItemsByOrderId($order['id']);
            $item1 = reset($items);
            $params = array(
                'targetId' => $item1['target_id'],
                'num' => $item1['num'],
                'unit' => $item1['unit'],
            );
            $product = $this->getOrderFacadeService()->getOrderProduct($item1['target_type'], $params);
            $paidSuccessUrlH5 = $this->generateUrl($product->successUrl[0], $product->successUrl[1]);
        } else {
            $paidSuccessUrlH5 = $this->generateUrl('my_coin');
        }

        return array(
            'platformCreatedResult' => json_encode($trade['platform_created_resul']),
            'paidSuccessUrlH5' => $paidSuccessUrlH5,
        );
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->biz->service('User:UserService');
    }
}
