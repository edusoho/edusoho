<?php

namespace ApiBundle\Api\Resource\Token;

use ApiBundle\Api\Exception\BannedCredentialException;
use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Exception\ResourceNotFoundException;
use ApiBundle\Api\Resource\Resource;
use ApiBundle\Api\Util\BrowserDetectionUtil;
use AppBundle\Common\EncryptionToolkit;
use Biz\User\Service\TokenService;
use Symfony\Component\HttpFoundation\Request;

class Token extends Resource
{
    public function add(Request $request)
    {
        $username = $request->request->get('username');
        $password =  $request->request->get('password');
        $password = EncryptionToolkit::XXTEADecrypt(base64_decode($password), 'edusoho');

        $user = $this->checkParams($username, $password);

        $args = array(
            'userId' => $user['id'],
            'device' => $this->getDevice($request)
        );

        $token = $this->getTokenService()->makeApiAuthToken($args);

        return array(
            'token' => $token['token'],
            'userId' => $user['id']
        );
    }

    private function checkParams($username, $password)
    {
        $user = $this->getUserService()->getUserByLoginField($username);
        if (empty($user)) {
            throw new ResourceNotFoundException('用户帐号不存在');
        }

        if (!$this->getUserService()->verifyPassword($user['id'], $password)) {
            throw new InvalidArgumentException('帐号密码不正确');
        }

        if ($user['locked']) {
            throw new BannedCredentialException('用户已锁定，请联系网校管理员');
        }

        return $user;
    }

    private function getDevice(Request $request)
    {

        $userAgent = $request->headers->get('User-Agent');
        preg_match("/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|
                    iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|
                    philips|phone|playbook|sagem|sharp|sie-|silk|smartphone|sony|symbian|t-mobile|telus|up\.browser|
                    up\.link|vodafone|wap|webos|wireless|xda|xoom|zte)/i", $userAgent, $matches);

        if ($matches) {
            return current($matches);
        } else {
            $bdu = new BrowserDetectionUtil($userAgent);
            $bdu->detect();
            $browser = $bdu->getBrowser();
            return $browser ? : TokenService::DEVICE_UNKNOWN;
        }
    }

    private function getTokenService()
    {
        return $this->service('User:TokenService');
    }

    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}