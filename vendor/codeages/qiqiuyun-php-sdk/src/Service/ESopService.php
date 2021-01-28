<?php

namespace QiQiuYun\SDK\Service;

class ESopService extends BaseService
{
    protected $host = 'esop-service.qiqiuyun.net';

    public function getTraceScript($data)
    {
        return $this->request('POST', '/api/v1/site_trace', $data);
    }

    /**
     * @param $data
     * @return array
     *      * action
     *      * data array
     */
    public function submitEventTracking($data)
    {
        return $this->request('POST', '/api/v1/event_tracking', $data);
    }
}
