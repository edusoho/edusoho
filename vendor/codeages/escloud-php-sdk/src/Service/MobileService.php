<?php

namespace ESCloud\SDK\Service;

use ESCloud\SDK\Exception\ResponseException;
use ESCloud\SDK\Exception\SDKException;
use ESCloud\SDK\HttpClient\ClientException;

class InspectionService extends BaseService
{
    protected $host = 'mobile-service.qiqiuyun.net';

    /**
     * @param $code
     *        apple授权获得code
     * @param $params
     *        grantType authorization_code(默认) || refresh_token
     *        refresh_token 可选  刷新令牌
     *        redirect_uri 可选  回调路由
     *
     */
    public function getAuthToken($code, $params = [])
    {
        $params['code'] = $code;

        return $this->request('GET', '/apple/auth', $params);
    }
}
