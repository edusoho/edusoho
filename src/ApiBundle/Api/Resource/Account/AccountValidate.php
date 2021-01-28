<?php

namespace ApiBundle\Api\Resource\Account;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\EncryptionToolkit;
use Biz\Common\CommonException;
use Biz\User\Service\UserService;

class AccountValidate extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $password = $request->request->get('password', '');

        if (empty($password)) {
            throw CommonException::ERROR_PARAMETER();
        }

        $user = $this->getCurrentUser();
        $password = EncryptionToolkit::XXTEADecrypt(base64_decode($password), $request->getHttpRequest()->getHost());
        if (!$this->getUserService()->verifyPassword($user['id'], $password)) {
            return false;
        }

        return true;
    }

    /**
     * @return UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}
