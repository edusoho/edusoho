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

    public function getStaffBindUrl($ticket)
    {
        return $this->request('GET', '/api/external/staff/getBindOauthUrl', array('ticket' => $ticket));
    }

    public function getStaffQrCode($staffId)
    {
        return $this->request('GET', '/api/external/staff/getQrCode', array('staffId' => $staffId));
    }

    public function getWechatOauthLoginUrl($ticket, $redirectUri, $thirdRedirectUrl)
    {
        return $this->request('GET', '/api/external/customer/getWechatOauthLoginUrl', array('ticket' => $ticket, 'redirectUri' => $redirectUri, 'thirdRedirectUrl' => $thirdRedirectUrl));
    }

    public function getCustomer($ticket)
    {
        return $this->request('GET', '/api/external/customer/get', array('ticket' => $ticket));
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

    public function uploadUserMessage($data)
    {
        return $this->request('POST', '/api/external/customerDynamic/report', $data);
    }
}
