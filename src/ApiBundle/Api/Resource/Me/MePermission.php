<?php

namespace ApiBundle\Api\Resource\Me;

use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

class MePermission extends AbstractResource
{
    public function search(ApiRequest $request)
    {
        $permissions = $request->query->get('permissions', []);
        $ownPermissions = $this->getCurrentUser()->getPermissions();
        $validPermissions = [];
        foreach ($permissions as $permission) {
            if (!empty($ownPermissions[$permission])) {
                $validPermissions[] = $permission;
            }
        }

        return $validPermissions;
    }
}
