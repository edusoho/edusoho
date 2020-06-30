<?php

namespace ESCloud\SDK\Service;

use ESCloud\SDK\Exception\ResponseException;
use ESCloud\SDK\Exception\SDKException;
use ESCloud\SDK\HttpClient\ClientException;

class MobileService extends BaseService
{
    protected $host = 'mobile-service.qiqiuyun.net';

    /**
     * @param $code
     *        apple授权获得code
     * @param $params
     *        grantType authorization_code(默认) || refresh_token
     *        refresh_token 可选  刷新令牌
     *        redirect_uri 可选  回调路由
     *  @return array 返回 apple验权信息
     *               * "access_token" apple为后续预留token,
     *               * "token_type" 固定值
     *               * "expires_in" 过期时间
     *               * "refresh_token" 生成新的访问令牌的刷新令牌
     *               * "id_token" 包含用户身份信息的JWT
     *
     */
    public function getAppleToken($code, $params = [])
    {
        $params['code'] = $code;

        return $this->request('GET', '/apple/auth', $params);
    }
}
