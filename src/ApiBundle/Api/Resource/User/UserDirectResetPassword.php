<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\System\Service\LogService;
use Biz\User\UserException;

class UserDirectResetPassword extends AbstractResource
{
    public function add(ApiRequest $request, $id)
    {
        $fields = $request->request->all();
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isSuperAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }
        $user = $this->getUserService()->getUser($id);
        if (empty($user)) {
            throw UserException::NOTFOUND_USER();
        }
        $this->getUserService()->changePassword($id, $fields['password']);
        $this->getLogService()->info('user', 'password-reset', "第三方api修改id为{$id}的密码");

        return 'success';
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }

    /**
     * @return LogService
     */
    private function getLogService()
    {
        return $this->service('System:LogService');
    }
}
