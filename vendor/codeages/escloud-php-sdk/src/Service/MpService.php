<?php

namespace ESCloud\SDK\Service;

class MpService extends BaseService
{
    protected $host = 'mp-service.qiqiuyun.net';
    protected $service = 'mp';

    public function getToken(array $params)
    {
        return $this->request('POST', '/tokens', $params);
    }

    public function getAuthorization()
    {
        return $this->request('GET', '/authorizations', array());
    }
}
