<?php

namespace ESCloud\SDK\Service;

class ScrmService extends BaseService
{
    protected $host = 'scrm-service.qiqiquyu.net';

    protected $service = 'scrm';


    public function getUserByToken($token)
    {
         return $this->request('GET',  '/api/console/customer/byToken', array('token' => $token));
    }

    public function verifyOrder($orderId, $token)
    {
        return $this->request('GET', '/api/console/order/verify_data', array('orderId' => $orderId, 'token' => $token));
    }

    public function callbackTrading($callbackData)
    {
        return $this->request('POST', '/api/console/order/callback', $callbackData);
    }

}
