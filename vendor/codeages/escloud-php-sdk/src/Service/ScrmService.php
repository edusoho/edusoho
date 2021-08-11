<?php

namespace ESCloud\SDK\Service;

class ScrmService extends BaseService
{
    protected $host = 'scrm.edusoho.com';

    protected $service = 'scrm';

    public function isScrmBind()
    {
        return $this->request('GET', '/api/external/corpBind/get');
    }

    public function getStaff($ticket)
    {
        return $this->request('GET', '/api/external/staff/get', array('ticket' => $ticket));
    }

    public function getStaffBindUrl($ticket, $redirectUri)
    {
        return $this->request('GET', '/api/external/staff/getBindAuthUrl', array('ticket' => $ticket, 'redirectUri' => $redirectUri));
    }

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
