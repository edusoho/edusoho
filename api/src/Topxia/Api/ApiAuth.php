<?php

namespace Topxia\Api;

use Biz\Role\Util\PermissionBuilder;
use Biz\User\CurrentUser;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;

class ApiAuth
{
    private $whilelist;

    public function __construct($whilelist)
    {
        $this->whilelist = $whilelist;
    }

    public function auth(Request $request)
    {
        $token = $request->headers->get('X-Auth-Token');

        if (empty($token)) {
            // 兼容老的协议，即将去除
            $token = $request->headers->get('Auth-Token', '');
        }

        $method = strtolower($request->headers->get('X-Auth-Method'));

        if ($method == 'keysign') {
            $this->setCurrentUser(array(
                'id' => 0,
                'nickname' => '游客',
                'currentIp' => $request->getClientIp(),
                'roles' => array(),
            ));

            $decoded = $this->decodeKeysign($token);
            $this->onlineSample($request, $token, 0);
        } else {
            $whilelist = isset($this->whilelist[$request->getMethod()]) ? $this->whilelist[$request->getMethod()] : array();

            $path = rtrim($request->getPathInfo(), '/');

            $inWhiteList = 0;

            foreach ($whilelist as $pattern) {
                if (preg_match($pattern, $path)) {
                    $inWhiteList = 1;
                    break;
                }
            }

            if (!$inWhiteList && empty($token)) {
                throw new \RuntimeException('API Token不存在！', 4);
            }

            $token = $this->getUserService()->getToken('mobile_login', $token);

            if (!$inWhiteList && empty($token['userId'])) {
                throw new \RuntimeException('API Token不正确！', 4);
            }

            $user = $this->getUserService()->getUser($token['userId']);

            if (!$inWhiteList && empty($user)) {
                throw new \RuntimeException('登录用户不存在！', 10);
            }

            if ($user) {
                $user['currentIp'] = $request->getClientIp();
            }

            $this->setCurrentUser($user);
            $this->onlineSample($request, $token['token'], $user['id']);
        }
    }

    protected function onlineSample($request, $token, $userId)
    {
        $userAgent = $request->headers->get('User-Agent', '');
        // 气球鱼爬虫不统计在线人数
        if (!$userAgent || strpos($userAgent, 'QiQiuYun Search Spider') >= 0) {
            return;
        }

        $online = array(
            'sess_id' => $token,
            'user_id' => $userId,
            'ip' => $request->getClientIp(),
            'user_agent' => $userAgent,
            'source' => 'App',
        );
        $this->getOnlineService()->saveOnline($online);
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

    private function getOnlineService()
    {
        return ServiceKernel::instance()->createService('Session:OnlineService');
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

    private function setCurrentUser($user)
    {
        $currentUser = new CurrentUser();

        if (empty($user)) {
            $user = array(
                'id' => 0,
                'nickname' => '游客',
                'currentIp' => '',
                'roles' => array(),
            );
        }

        $currentUser->fromArray($user);
        $currentUser->setPermissions(PermissionBuilder::instance()->getPermissionsByRoles($currentUser->getRoles()));
        ServiceKernel::instance()->setCurrentUser($currentUser);
    }
}
