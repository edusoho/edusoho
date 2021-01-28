<?php

namespace Biz\User;

use Symfony\Component\Security\Core\User\UserInterface;

class AnonymousUser extends CurrentUser
{
    public function __construct($user = array())
    {
        $user = array_merge(array(
            'id' => 0,
            'currentIp' => '127.0.0.1',
            'nickname' => '游客',
            'email' => 'test.edusoho.com',
            'roles' => array(),
            'locked' => false,
            'org' => array('id' => $this->rootOrgId, 'orgCode' => $this->rootOrgCode),
            'orgId' => $this->rootOrgId,
            'orgCode' => $this->rootOrgCode,
            'password' => '',
        ), $user);

        $this->data = $user;
    }

    public function serialize()
    {
        return parent::serialize();
    }

    public function unserialize($serialized)
    {
        parent::unserialize($serialized);
    }

    public function __set($name, $value)
    {
        return parent::__set($name, $value);
    }

    public function __isset($name)
    {
        return parent::__isset($name);
    }

    public function __unset($name)
    {
        parent::__unset($name);
    }

    public function clearNotifacationNum()
    {
        parent::clearNotifacationNum();
    }

    public function clearMessageNum()
    {
        parent::clearMessageNum();
    }

    public function offsetExists($offset)
    {
        return parent::offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return parent::offsetGet($offset);
    }

    public function offsetSet($offset, $value)
    {
        return parent::offsetSet($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return parent::offsetUnset($offset);
    }

    public function getRoles()
    {
        return array();
    }

    public function getPassword()
    {
        return '';
    }

    public function getSalt()
    {
        return '';
    }

    public function getUsername()
    {
        return '游客';
    }

    public function getId()
    {
        return 0;
    }

    public function isAccountNonExpired()
    {
        return true;
    }

    public function isAccountNonLocked()
    {
        return true;
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
        return parent::getLocale();
    }

    public function isEqualTo(UserInterface $user)
    {
        return parent::isEqualTo($user);
    }

    public function isLogin()
    {
        return false;
    }

    public function isAdmin()
    {
        return false;
    }

    public function isSuperAdmin()
    {
        return false;
    }

    public function isTeacher()
    {
        return false;
    }

    public function setPermissions($permissions)
    {
        return $this;
    }

    public function getPermissions()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function hasPermission($code)
    {
        return false;
    }
}
