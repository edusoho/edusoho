<?php

namespace AppBundle\Controller\Callback\CloudSearch;

use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\Callback\Resource;

abstract class BaseResource extends Resource
{
    public function auth(Request $request)
    {
        $token = $request->headers->get('X-Auth-Token');
        $this->decodeKeysign($token);
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

    protected function nextCursorPaging($currentCursor, $currentStart, $currentLimit, $currentRows)
    {
        $end = end($currentRows);
        if (empty($end)) {
            return array(
                'cursor' => $currentCursor + 1,
                'start' => 0,
                'limit' => $currentLimit,
                'eof' => true,
            );
        }

        if (count($currentRows) < $currentLimit) {
            return array(
                'cursor' => $end['updatedTime'] + 1,
                'start' => 0,
                'limit' => $currentLimit,
                'eof' => true,
            );
        }

        if ($end['updatedTime'] != $currentCursor) {
            $next = array(
                'cursor' => $end['updatedTime'],
                'start' => 0,
                'limit' => $currentLimit,
                'eof' => false,
            );
        } else {
            $next = array(
                'cursor' => $currentCursor,
                'start' => $currentStart + $currentLimit,
                'limit' => $currentLimit,
                'eof' => false,
            );
        }

        return $next;
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
