<?php

namespace ApiBundle\Api\Annotation;

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
                throw new \BadMethodCallException(sprintf('Unknown property "%s" on annotation "%s".', $key, get_class($this)));
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
