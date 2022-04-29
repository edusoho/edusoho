<?php

namespace MarketingMallBundle\Security\Firewall;

use ApiBundle\Security\Firewall\XAuthTokenAuthenticationListener as BaseListener;
use Firebase\JWT\JWT;
use Http\Discovery\Exception\NotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Common\ServiceKernel;

class XAuthTokenAuthenticationListener extends BaseListener
{
    const MALL_TOKEN_HEADER = 'Mall-Auth-Token';

    public function handle(Request $request)
    {
        if (null != $tokenInHeader = $request->headers->get(self::MALL_TOKEN_HEADER)) {
//            $mallSettings = $this->getSettingService()->get('marketing_mall', []);
//            $storages = $this->getSettingService()->get('storages', []);
//            try {
//                if (empty($mallSettings['secret_key'])) {
//                    $result = JWT::decode($tokenInHeader, $storages['cloud_secret_key'], array('HS256'));
//                    $access_key = $storages['cloud_access_key'];
//                }else{
//                    $result = JWT::decode($tokenInHeader, $mallSettings['secret_key'], array('HS256'));
//                    $access_key = $mallSettings['access_key'];
//                }
//            }catch (\RuntimeException $e){
//                throw new NotFoundException('token 鉴权失败！');
//            }
//
//            if($result->access_key != $access_key){
//                throw new NotFoundException('token 鉴权失败！');
//            }
//            if (empty($result->user_id)) {
//                throw new NotFoundException('user_id 不存在！');
//            }
            $token = $this->createTokenFromRequest($request, 2);
            $this->getTokenStorage()->setToken($token);
        } else {
            parent::handle($request);
        }
    }

    protected function getSettingService()
    {
        return ServiceKernel::instance()->getBiz()->service('System:SettingService');
    }
}
