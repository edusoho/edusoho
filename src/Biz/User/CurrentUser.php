<?php

namespace Biz\User;

use Biz\Role\Util\PermissionBuilder;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Common\Exception\UnexpectedValueException;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;

class CurrentUser implements AdvancedUserInterface, EquatableInterface, \ArrayAccess, \Serializable
{
    protected $data;
    protected $permissions;

    protected $rootOrgId = 1;

    protected $rootOrgCode = '1.';

    protected $context = array();

    public function serialize()
    {
        return serialize($this->data);
    }

    public function unserialize($serialized)
    {
        $this->data = unserialize($serialized);
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;

        return $this;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }
        throw new UnexpectedValueException("{$name} is not exist in CurrentUser.");
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

    public function getLocale()
    {
        return $this->locale;
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

        return !empty($permissions) && (array_key_exists('admin', $permissions) || array_key_exists('admin_v2', $permissions));
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

    public function getOrg()
    {
        return empty($this->orgId) ? $this->org['orgId'] : $this->orgId;
    }

    public function getOrgCode()
    {
        $org = $this->getOrg();

        return $org['orgCode'];
    }

    public function getOrgId()
    {
        $org = $this->getOrg();

        return $org['id'];
    }

    public function getSelectOrgCode()
    {
        $selectOrg = $this->getSelectOrg();

        return $selectOrg['orgCode'];
    }

    public function getSelectOrgId()
    {
        $selectOrg = $this->getSelectOrg();

        return $selectOrg['id'];
    }

    public function fromArray(array $user)
    {
        if (empty($user['org'])) {
            $user['org'] = array('id' => $this->rootOrgId, 'orgCode' => $this->rootOrgCode);
            $user['orgId'] = $this->rootOrgId;
            $user['orgCode'] = $this->rootOrgCode;
        }
        $this->data = $user;

        return $this;
    }

    public function toArray()
    {
        return $this->data;
    }

    public function setPermissions($permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    public function setContext($name, $value)
    {
        $this->context[$name] = $value;
    }

    public function getContext($name)
    {
        return isset($this->context[$name]) ? $this->context[$name] : null;
    }

    /**
     * @param string $code 权限编码
     *
     * @return bool
     */
    public function hasPermission($code)
    {
        $currentUserPermissions = $this->getPermissions();

        if (!empty($currentUserPermissions[$code])) {
            return true;
        }

        $tree = PermissionBuilder::instance()->getOriginPermissionTree(true);
        $codeTree = $tree->find(function ($tree) use ($code) {
            return $tree->data['code'] === $code;
        });

        if (empty($codeTree)) {
            return false;
        }

        $disableTree = $codeTree->findToParent(function ($parent) {
            return isset($parent->data['disable']) && (bool) $parent->data['disable'];
        });

        if (is_null($disableTree)) {
            return false;
        }

        $parent = $disableTree->getParent();

        if (is_null($parent)) {
            return false;
        }

        if (empty($parent->data['parent'])) {
            return true;
        } else {
            return !empty($currentUserPermissions[$parent->data['code']]);
        }
    }
}
