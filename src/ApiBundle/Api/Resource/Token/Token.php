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

        $token = $this->getUserService()->makeToken(TokenService::TYPE_MOBILE_LOGIN, $user['id'], time() + 3600 * 24 * 30);

        return array(
            'token' => $token,
            'userId' => $user['id']
        );
    }

    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}