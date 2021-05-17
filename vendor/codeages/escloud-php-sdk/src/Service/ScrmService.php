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

    public function verifyOrder($token)
    {
        return $this->request('POST', '', array());
    }

}
