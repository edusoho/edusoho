<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\EncryptionToolkit;
use Biz\Common\CommonException;
use Biz\User\Service\AuthService;
use Biz\User\UserException;

class UserRegister extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isSuperAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }
        $fields = $request->request->all();
        if (empty($fields['email']) && empty($fields['mobile'])) {
            CommonException::ERROR_PARAMETER_MISSING();
        }
        if (empty($fields['nickname']) || empty($fields['encrypt_password'])) {
            CommonException::ERROR_PARAMETER_MISSING();
        }
        $user = [
            'mobile' => !empty($fields['mobile']) ? $fields['mobile'] : '',
            'verifiedMobile' => !empty($fields['mobile']) ? $fields['mobile'] : '',
            'email' => !empty($fields['email']) ? $fields['email'] : '',
            'emailOrMobile' => !empty($fields['mobile']) ? $fields['mobile'] : $fields['email'],
            'nickname' => $fields['nickname'],
            'password' => $this->getPassword($fields['encrypt_password'], $request->getHttpRequest()->getHost()),
            'registeredWay' => '',
            'registerVisitId' => empty($fields['registerVisitId']) ? '' : $fields['registerVisitId'],
            'createdIp' => $request->getHttpRequest()->getClientIp(),
        ];

        return $this->getAuthService()->register($user);
    }

    private function getPassword($password, $host)
    {
        return EncryptionToolkit::XXTEADecrypt(base64_decode($password), $host);
    }

    /**
     * @return AuthService
     */
    protected function getAuthService()
    {
        return $this->service('User:AuthService');
    }
}
