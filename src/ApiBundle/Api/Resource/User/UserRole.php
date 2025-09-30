<?php

namespace ApiBundle\Api\Resource\User;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Common\CommonException;
use Biz\User\UserException;

class UserRole extends AbstractResource
{
    public function add(ApiRequest $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isSuperAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }
        $fields = $request->request->all();
        if (!ArrayToolkit::requireds($fields, ['roles'], true)) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        if (!in_array('ROLE_USER', $fields['roles'])) {
            $fields['roles'] = array_merge(['ROLE_USER'], $fields['roles']);
        }
        $this->getUserService()->changeUserRoles($id, $fields['roles']);

        return true;
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    private function getUserService()
    {
        return $this->service('User:UserService');
    }
}
