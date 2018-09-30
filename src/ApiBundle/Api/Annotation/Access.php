<?php

namespace ApiBundle\Api\Annotation;

use Biz\Common\CommonException;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
class Access
{
    private $roles;

    private $rolesArray = array('ROLE_USER');

    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set'.str_replace('_', '', $key);
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

    /**
     * @param $currentUserRoles
     *
     * @return bool
     */
    public function canAccess($currentUserRoles)
    {
        $roles = $this->rolesArray = explode(',', $this->roles);
        if (empty($roles)) {
            return true;
        }
        foreach ($roles as $role) {
            if (in_array($role, $currentUserRoles)) {
                return true;
            }
        }

        return false;
    }
}
