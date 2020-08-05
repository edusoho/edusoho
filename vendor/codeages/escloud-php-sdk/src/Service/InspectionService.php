<?php
namespace ESCloud\SDK\Service;

use ESCloud\SDK\Exception\ResponseException;
use ESCloud\SDK\Exception\SDKException;
use ESCloud\SDK\HttpClient\ClientException;

class InspectionService extends BaseService
{
    protected $host = 'inspection-service.qiqiuyun.net';
    protected $service = 'inspection';

    /**
     * 获得帐户信息
     *
     * @return array
     * @throws ResponseException
     * @throws SDKException
     * @throws ClientException
     */
    public function getAccount()
    {
        return $this->request('GET', '/api/account');
    }
}
