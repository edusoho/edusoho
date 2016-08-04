<?php
namespace Topxia\Service\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class CurrentUser implements AdvancedUserInterface, EquatableInterface, \ArrayAccess
{
    protected $data;
    protected $permissions;

    protected $rootOrgId = 1;

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->data)) {
            $this->data[$name] = $value;
        }

        throw new \RuntimeException("{$name} is not exist in CurrentUser.");
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        throw new \RuntimeException("{$name} is not exist in CurrentUser.");
    }

    public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    public function __unset($name)
    {
        unset($this->data[$name]);
    }

    public function clearNotifacationNum()
    {
        $this->data['newNotificationNum'] = '0';
    }

    public function clearMessageNum()
    {
        $this->data['newMessageNum'] = '0';
    }

    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    public function offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function getUsername()
    {
        return $this->email;
    }

    public function getId()
    {
        return $this->id;
    }

    public function eraseCredentials()
    {
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return !$this->locked;
    }

    public function isCredentialsNonExpired()
    {
        return true;
    }

    public function isEnabled()
    {
        return true;
    }

    public function isEqualTo(UserInterface $user)
    {
        if ($this->email !== $user->getUsername()) {
            return false;
        }

        if (array_diff($this->roles, $user->getRoles())) {
            return false;
        }

        if (array_diff($user->getRoles(), $this->roles)) {
            return false;
        }

        return true;
    }

    public function isLogin()
    {
        return empty($this->id) ? false : true;
    }

    public function isAdmin()
    {
        $permissions = $this->getPermissions();
        if (!empty($permissions) && in_array('admin', array_keys($permissions))) {
            return true;
        }

        return false;
    }

    public function isSuperAdmin()
    {
        if (count(array_intersect($this->getRoles(), array('ROLE_SUPER_ADMIN'))) > 0) {
            return true;
        }

        return false;
    }

    public function isTeacher()
    {
        $permissions = $this->getPermissions();
        return in_array('web', array_keys($permissions));
    }

    public function getCurrentOrgId()
    {
        $currentOrg = $this->getCurrentOrg();
        return $currentOrg['id'];
    }

    public function getCurrentOrg()
    {
        return $this->org;
    }

    public function getSelectOrg()
    {
        return isset($this->selectOrg) ? $this->selectOrg : $this->org;
    }

    public function getSelectOrgCode()
    {
        return isset($this->selectOrgCode) ? $this->selectOrgCode : $this->orgCode;
    }

    public function getCurrentOrgCode()
    {
        return $this->orgCode;
    }

    public function getSelectOrgId()
    {
        $selectOrg = $this->getSelectOrg();
        return $selectOrg['id'];
    }

    public function fromArray(array $user)
    {
        if (empty($user['org'])) {
            $user['org'] = array('id' => $this->rootOrgId);
        }
        $this->data = $user;

        $roles = $this->getRoles();
        if ($this->isAdmin()) {
            $roles[]             = 'ROLE_ADMIN';
            $this->data['roles'] = $roles;
        }

        return $this;
    }

    public function toArray()
    {
        return $this->data;
    }

    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }
}
