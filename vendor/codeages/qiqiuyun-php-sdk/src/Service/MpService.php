<?php

namespace QiQiuYun\SDK\Service;

class MpService extends BaseService
{
    protected $host = 'mp-service.qiqiuyun.net';

    public function sendMpRequest(array $params)
    {
        return $this->request('POST', '/mpRequests', $params);
    }

    public function getCurrentMpRequest()
    {
        return $this->request('GET', '/mpRequests/current', array());
    }
}
