<?php

namespace Tests\Unit\User;

use Biz\BaseTestCase;
use Biz\User\AnonymousUser;

class AnonymousUserTest extends BaseTestCase
{
    public function testSerialize()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->serialize();

        $this->assertEquals('a:1', substr($result, 0, 3));
    }

    public function testUnserialize()
    {
        $anonymousUser = new AnonymousUser();
        $serialized = $anonymousUser->serialize();

        $anonymousUser->unserialize($serialized);

        $this->assertEquals('游客', $anonymousUser->__get('nickname'));
    }

    public function testSet()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->__set('nickname', 'testname');

        $this->assertEquals('testname', $anonymousUser->__get('nickname'));
    }

    public function testGet()
    {
        $anonymousUser = new AnonymousUser();
        $result1 = $anonymousUser->__get('password');

        $this->assertEquals('', $result1);

        $result2 = $anonymousUser->__get('nickname');

        $this->assertEquals('游客', $result2);
    }

    public function testIsset()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->__isset('nickname');

        $this->assertTrue($result);
    }

    public function testUnset()
    {
        $anonymousUser = new AnonymousUser();
        $anonymousUser->__unset('nickname');
        $result = $anonymousUser->__isset('nickname');

        $this->assertFalse($result);
    }

    public function testClearNotifacationNum()
    {
        $anonymousUser = new AnonymousUser();
        $anonymousUser->clearNotifacationNum();

        $this->assertEquals(0, $anonymousUser->__get('newNotificationNum'));
    }

    public function testClearMessageNum()
    {
        $anonymousUser = new AnonymousUser();
        $anonymousUser->clearMessageNum();

        $this->assertEquals(0, $anonymousUser->__get('newMessageNum'));
    }

    public function testOffsetExists()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->offsetExists('nickname');

        $this->assertTrue($result);
    }

    public function testOffsetGet()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->offsetGet('nickname');

        $this->assertEquals('游客', $result);
    }

    public function testOffsetSet()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->offsetSet('nickname', 'testname');

        $this->assertEquals('testname', $anonymousUser->offsetGet('nickname'));
    }

    public function testOffsetUnset()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->offsetUnset('nickname');

        $this->assertNull($result);
    }

    public function testGetRoles()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->getRoles();

        $this->assertEquals(array(), $result);
    }

    public function testGetPassword()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->getPassword();

        $this->assertEquals('', $result);
    }

    public function testGetSalt()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->getSalt();

        $this->assertEquals('', $result);
    }

    public function testGetUsername()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->getUsername();

        $this->assertEquals('游客', $result);
    }

    public function testGetId()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->getId();

        $this->assertEquals(0, $result);
    }

    public function testIsAccountNonExpired()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->isAccountNonExpired();

        $this->assertTrue($result);
    }

    public function testIsAccountNonLocked()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->isAccountNonLocked();

        $this->assertTrue($result);
    }

    public function testIsCredentialsNonExpired()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->isCredentialsNonExpired();

        $this->assertTrue($result);
    }

    public function testIsEnabled()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->isEnabled();

        $this->assertTrue($result);
    }

    public function testIsEqualTo()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->isEqualTo($this->getCurrentUser());

        $this->assertFalse($result);
    }

    public function testIsLogin()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->isLogin();

        $this->assertFalse($result);
    }

    public function testIsAdmin()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->isAdmin();

        $this->assertFalse($result);
    }

    public function testIsSuperAdmin()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->isSuperAdmin();

        $this->assertFalse($result);
    }

    public function testIsTeacher()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->isTeacher();

        $this->assertFalse($result);
    }

    public function testGetCurrentOrgId()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->getCurrentOrgId();

        $this->assertEquals(1, $result);
    }

    public function testGetCurrentOrg()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->getCurrentOrg();

        $this->assertEquals(array('id' => 1, 'orgCode' => '1.'), $result);
    }

    public function testGetSelectOrg()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->getSelectOrg();

        $this->assertEquals(array('id' => 1, 'orgCode' => '1.'), $result);
    }

    public function testGetOrg()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->getOrg();

        $this->assertEquals(1, $result);
    }

    public function testGetOrgCode()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->getOrgCode();

        $this->assertEquals(null, $result);
    }

    public function testGetOrgId()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->getOrgId();

        $this->assertEquals(null, $result);
    }

    public function testGetSelectOrgCode()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->getSelectOrgCode();

        $this->assertEquals('1.', $result);
    }

    public function testGetSelectOrgId()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->getSelectOrgId();

        $this->assertEquals(1, $result);
    }

    public function testFromArray()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->fromArray(array());

        $this->assertEquals('Biz\User\AnonymousUser', get_class($result));
    }

    public function testToArray()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->toArray();

        $this->assertEquals(0, $result['id']);
    }

    public function testSetPermissions()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->setPermissions(array());

        $this->assertEquals('Biz\User\AnonymousUser', get_class($result));
    }

    public function testGetPermissions()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->getPermissions();

        $this->assertEquals(array(), $result);
    }

    public function testHasPermission()
    {
        $anonymousUser = new AnonymousUser();
        $result = $anonymousUser->hasPermission('');

        $this->assertFalse($result);
    }
}
