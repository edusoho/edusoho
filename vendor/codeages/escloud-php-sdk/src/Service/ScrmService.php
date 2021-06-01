<?php

namespace ESCloud\SDK\Service;

class ScrmService extends BaseService
{
    protected $host = 'scrm.edusoho.com';

    protected $service = 'scrm';


    public function getUserByToken($token)
    {
         return $this->request('GET',  '/api/console/customer/byToken', array('token' => $token));
    }

    public function verifyOrder($orderId, $token)
    {
        return $this->request('GET', '/api/console/order/verifyData', array('orderId' => $orderId, 'token' => $token));
    }

    public function callbackTrading($callbackData)
    {
        return $this->request('POST', '/api/console/order/callback', $callbackData);
    }

}
