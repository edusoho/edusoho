<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Common\CommonException;
use Biz\User\UserException;

class UserToggleAccountLocked extends AbstractResource
{
    public function add(ApiRequest $request, $id, $type)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isSuperAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }
        if (!in_array($type, ['locked', 'unlocked'])) {
            throw CommonException::ERROR_PARAMETER();
        }
        if ('locked' == $type) {
            $this->getUserService()->lockUser($id);
        } else {
            $this->getUserService()->unlockUser($id);
        }

        return 'success';
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
}
