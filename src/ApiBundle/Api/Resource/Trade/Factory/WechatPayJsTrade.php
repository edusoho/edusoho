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
