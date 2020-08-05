<?php

namespace ESCloud\SDK\Service;

class ESopService extends BaseService
{
    protected $host = 'esop-service.qiqiuyun.net';

    private $siteTracePath = '/api/v1/site_trace';

    protected $service = 'esop';

    public function getTraceScript($data)
    {
        return $this->request('POST', $this->siteTracePath, $data);
    }
}
