<?php

namespace ApiBundle\Api\Annotation;

use Biz\Common\CommonException;
use Biz\User\CurrentUser;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Access
{
    private $roles;

    private $permissions;

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', $key);
            if (!method_exists($this, $method)) {
                throw CommonException::NOTFOUND_METHOD();
            }
            $this->$method($value);
        }
    }

    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * @param CurrentUser $currentUser
     *
     * @return bool
     */
    public function canAccess(CurrentUser $currentUser)
    {
        if (empty($this->roles) && empty($this->permissions)) {
            return true;
        }
        if (!empty($this->roles) && $this->canAccessByRoles($currentUser)) {
            return true;
        }
        if (!empty($this->permissions) && $this->canAccessByPermissions($currentUser)) {
            return true;
        }

        return false;
    }

    private function canAccessByRoles(CurrentUser $currentUser)
    {
        $roles = explode(',', $this->roles);
        if (empty($roles)) {
            return true;
        }
        foreach ($roles as $role) {
            if (in_array($role, $currentUser->getRoles())) {
                return true;
            }
        }

        return false;
    }

    private function canAccessByPermissions(CurrentUser $currentUser)
    {
        $permissions = explode(',', $this->permissions);
        if (empty($permissions)) {
            return true;
        }
        foreach ($permissions as $permission) {
            if ($currentUser->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
