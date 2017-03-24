<?php

namespace ApiBundle\Security;

use Biz\User\Service\TokenService;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class ApiAuth
{

    public function auth(Request $request)
    {
        $token = $request->headers->get('X-Auth-Token');

        $method = strtolower($request->headers->get('X-Auth-Method'));

        if ($method == 'keysign') {
            $this->decodeKeysign($token);
            return array('allowed_without_user' => true);
        } else {

            if (FireWall::isInWhiteList($request)) {
                return array('allowed_without_user' => true);
            }

            if (empty($token)) {
                throw new \RuntimeException('API Token不存在！');
            }

            $token = $this->getUserService()->getToken(TokenService::TYPE_API_AUTH, $token);

            if (empty($token['userId'])) {
                throw new \RuntimeException('API Token不正确！');
            }
        }

        return $token;
    }

    public function decodeKeysign($token)
    {
        $token = explode(':', $token);

        if (count($token) != 3) {
            throw new \RuntimeException('API Token格式不正确！');
        }

        list($accessKey, $policy, $sign) = $token;

        if (empty($accessKey) || empty($policy) || empty($sign)) {
            throw new \RuntimeException('API Token不正确！');
        }

        $settings = $this->getSettingService()->get('storage', array());

        if (empty($settings['cloud_access_key']) || empty($settings['cloud_secret_key'])) {
            throw new \RuntimeException('系统尚未配置AccessKey/SecretKey');
        }

        if ($accessKey != $settings['cloud_access_key']) {
            throw new \RuntimeException('AccessKey不正确！');
        }

        $expectedSign = $this->encodeBase64(hash_hmac('sha1', $policy, $settings['cloud_secret_key'], true));

        if ($sign != $expectedSign) {
            throw new \RuntimeException('API Token 签名不正确！');
        }

        $policy = json_decode($this->decodeBase64($policy), true);

        if (empty($policy)) {
            throw new \RuntimeException('API Token 解析失败！');
        }

        if (time() > $policy['deadline']) {
            throw new \RuntimeException(sprintf('API Token 已过期！(%s)', date('Y-m-d H:i:s')));
        }

        return $policy;
    }

    public function encodeKeysign($request, $role = 'guest', $lifetime = 600)
    {
        $settings = $this->getSettingService()->get('storage', array());

        $policy = array(
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'role' => $role,
            'deadline' => time() + $lifetime,
        );

        $encoded = $this->encodeBase64(json_encode($policy));

        $sign = hash_hmac('sha1', $encoded, $settings['cloud_secret_key'], true);

        return $settings['cloud_access_key'].':'.$encoded.':'.$this->encodeBase64($sign);
    }

    private function getSettingService()
    {
        return ServiceKernel::instance()->createService('System:SettingService');
    }

    private function getUserService()
    {
        return ServiceKernel::instance()->createService('User:UserService');
    }

    private function encodeBase64($string)
    {
        $find = array('+', '/');
        $replace = array('-', '_');

        return str_replace($find, $replace, base64_encode($string));
    }

    private function decodeBase64($string)
    {
        $find = array('-', '_');
        $replace = array('+', '/');

        return base64_decode(str_replace($find, $replace, $string));
    }

}
