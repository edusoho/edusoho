<?php
namespace ApiBundle\Api\Resource\UserResetPassword;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\EncryptionToolkit;
use AppBundle\Common\SimpleValidator;
use Biz\Common\CommonException;
use Biz\User\UserException;

class UserResetPassword extends AbstractResource
{
    public function add(ApiRequest $request)
    {
        $fields = $request->request->all();
        if (!ArrayToolkit::requireds($fields, [
            'oldPassword',
            'encryptPassword',
        ])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }

        $user = $this->getCurrentUser();
        if (!$user) {
            throw UserException::NOTFOUND_USER();
        }

        if (!$this->getUserService()->verifyPassword($user['id'], $fields['oldPassword'])) {
            throw UserException::PASSWORD_ERROR();
        }

        $password = EncryptionToolkit::XXTEADecrypt(base64_decode($fields['encryptPassword']), $request->getHttpRequest()->getHost());

        if (!SimpleValidator::highPassword($password)) {
            throw CommonException::ERROR_PARAMETER();
        }

        $this->getUserService()->changePassword($user['id'], $password);
        $this->getLogService()->info('user', 'password-reset', "{$user['id']}通过旧密码重置了密码。");

        return $user;
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    private function getLogService()
    {
        return $this->service('System:LogService');
    }

    private function getTokenService()
    {
        return $this->service('User:TokenService');
    }
}
