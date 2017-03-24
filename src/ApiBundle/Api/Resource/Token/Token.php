<?php

namespace ApiBundle\Api\Resource\Token;

use ApiBundle\Api\Exception\BannedCredentialException;
use ApiBundle\Api\Exception\InvalidArgumentException;
use ApiBundle\Api\Resource\Resource;
use AppBundle\Common\EncryptionToolkit;
use Biz\User\Service\TokenService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class Token extends Resource
{
    public function add(Request $request)
    {
        $username = $request->request->get('username');
        $password =  $request->request->get('password');

        if (empty($password)) {
            $password = $request->request->get('encrypt_password');
            $password = EncryptionToolkit::XXTEADecrypt(base64_decode($password), $request->getHost());
        }

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

    private function getDevice(Request $request)
    {
        $userAgent = $request->headers->get('User-Agent');
        preg_match("/iPhone|Android|iPad|iPod|webOS/", $userAgent, $matches);

        if ($matches) {
            return current($matches);
        }

        return TokenService::DEVICE_UNKNOWN;
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