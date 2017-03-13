<?php

namespace AppBundle\Controller\Callback;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Controller\BaseController;

class IndexController extends BaseController
{
    public function indexAction(Request $request, $resource)
    {
        $token = $request->headers->get('X-Auth-Token');
        $method = strtolower($request->headers->get('X-Auth-Method'));
        $this->checkToken($token, $method);

        $resourceInstance = $this->get('callback.resource_factory')->create($resource);
        $method = strtolower($request->getMethod());
        if (!in_array($method, array('post', 'get'))) {
            throw new \InvalidArgumentException(sprintf('unsupported method: %s', $method));
        }

        return new JsonResponse($resourceInstance->$method($request));
    }

    protected function checkToken($token, $method)
    {
        if ($method == 'keysign') {
            $this->decodeKeysign($token);
        } else {
            throw new \RuntimeException(sprintf('系统尚不支持此授权方式：%s', $method));
        }
    }

    protected function decodeKeysign($token)
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

    protected function encodeKeysign($request, $role = 'guest', $lifetime = 600)
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

    protected function encodeBase64($string)
    {
        $find = array('+', '/');
        $replace = array('-', '_');

        return str_replace($find, $replace, base64_encode($string));
    }

    protected function decodeBase64($string)
    {
        $find = array('-', '_');
        $replace = array('+', '/');

        return base64_decode(str_replace($find, $replace, $string));
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
