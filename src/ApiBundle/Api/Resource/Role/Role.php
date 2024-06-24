<?php

namespace ApiBundle\Api\Resource\role;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Role\Service\RoleService;
use Biz\User\UserException;

class Role extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $currentUser = $this->getCurrentUser();
        if (!$currentUser->isSuperAdmin()) {
            throw UserException::PERMISSION_DENIED();
        }

        $conditions = $request->query->all();

        return $this->getRoleService()->searchRoles($conditions, [], 0, PHP_INT_MAX);
    }

    /**
     * @return RoleService
     */
    protected function getRoleService()
    {
        return $this->service('Role:RoleService');
    }
}
