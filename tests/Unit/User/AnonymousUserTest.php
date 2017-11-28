<?php

namespace Tests\Unit\User;

use Biz\BaseTestCase;
use Biz\User\AnonymousUser;

class AnonymousUserTest extends BaseTestCase
{
    public function testSerialize()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->serialize();

        $this->assertEquals('a:9', substr($result, 0, 3));
    }

    public function testUnserialize()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $serialized = $anonymousUser->serialize();

        $anonymousUser->unserialize($serialized);

        $this->assertEquals('游客', $anonymousUser->__get('nickname'));
    }

    public function test__set()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->__set('nickname', 'testname');

        $this->assertEquals('testname', $anonymousUser->__get('nickname'));
    }

    public function test__get()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result1 = $anonymousUser->__get('password');

        $this->assertEquals('', $result1);

        $result2 = $anonymousUser->__get('nickname');

        $this->assertEquals('游客', $result2);
    }

    public function test__isset()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->__isset('nickname');

        $this->assertTrue($result);
    }

    public function test__unset()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $anonymousUser->__unset('nickname');
        $result = $anonymousUser->__isset('nickname');

        $this->assertFalse($result);
    }

    public function testClearNotifacationNum()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $anonymousUser->clearNotifacationNum();

        $this->assertEquals(0, $anonymousUser->__get('newNotificationNum'));
    }

    public function testClearMessageNum()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $anonymousUser->clearMessageNum();

        $this->assertEquals(0, $anonymousUser->__get('newMessageNum'));
    }

    public function testOffsetExists()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->offsetExists('nickname');

        $this->assertTrue($result);
    }

    public function testOffsetGet()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->offsetGet('nickname');

        $this->assertEquals('游客', $result);
    }

    public function testOffsetSet()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->offsetSet('nickname', 'testname');

        $this->assertEquals('testname', $anonymousUser->offsetGet('nickname'));
    }

    public function testOffsetUnset()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->offsetUnset('nickname');

        $this->assertNull($result);
    }

    public function testGetRoles()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->getRoles();

        $this->assertEquals(array(), $result);
    }

    public function testGetPassword()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->getPassword();

        $this->assertEquals('', $result);
    }

    public function testGetSalt()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->getSalt();

        $this->assertEquals('', $result);
    }

    public function testGetUsername()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->getUsername();

        $this->assertEquals('游客', $result);
    }

    public function testGetId()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->getId();

        $this->assertEquals(0, $result);
    }

    public function testIsAccountNonExpired()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->isAccountNonExpired();

        $this->assertTrue($result);
    }

    public function testIsAccountNonLocked()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->isAccountNonLocked();

        $this->assertTrue($result);
    }

    public function testIsCredentialsNonExpired()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->isCredentialsNonExpired();

        $this->assertTrue($result);
    }

    public function testIsEnabled()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->isEnabled();

        $this->assertTrue($result);
    }

    public function testIsEqualTo()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->isEqualTo($this->getCurrentUser());
        
        $this->assertFalse($result);
    }

    public function testIsLogin()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->isLogin();
        
        $this->assertFalse($result);
    }

    public function testIsAdmin()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->isAdmin();
        
        $this->assertFalse($result);
    }

    public function testIsSuperAdmin()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->isSuperAdmin();
        
        $this->assertFalse($result);
    }

    public function testIsTeacher()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->isTeacher();
        
        $this->assertFalse($result);
    }

    public function testGetCurrentOrgId()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->getCurrentOrgId();
        
        $this->assertEquals(0, $result);
    }

    public function testGetCurrentOrg()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->getCurrentOrg();

        $this->assertEquals(array(), $result);
    }

    public function testGetSelectOrg()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->getSelectOrg();

        $this->assertEquals(array(), $result);
    }

    public function testGetOrg()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->getOrg();

        $this->assertEquals(array('id' => 1, 'orgCode' => '1.'), $result);
    }

    public function testGetOrgCode()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->getOrgCode();

        $this->assertEquals('1.', $result);
    }

    public function testGetOrgId()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->getOrgId();

        $this->assertEquals(1, $result);
    }

    public function testGetSelectOrgCode()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->getSelectOrgCode();

        $this->assertEquals('1.', $result);
    }

    public function testGetSelectOrgId()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->getSelectOrgId();

        $this->assertEquals(1, $result);
    }

    public function testFromArray()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->fromArray(array());

        $this->assertEquals('Biz\User\AnonymousUser', get_class($result));
    }

    public function testToArray()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->toArray();

        $this->assertEquals(0, $result['id']);
    }

    public function testSetPermissions()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->setPermissions(array());

        $this->assertEquals('Biz\User\AnonymousUser', get_class($result));
    }

    public function testGetPermissions()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->getPermissions();

        $this->assertEquals(array(), $result);
    }

    public function testHasPermission()
    {
        $anonymousUser = new AnonymousUser('127.0.0.1');
        $result = $anonymousUser->hasPermission('');

        $this->assertFalse($result);
    }
}