<?php

namespace Tests\Unit\File\FireWall;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\File\FireWall\GroupFileFireWall;

class GroupFileFireWallTest extends BaseTestCase
{
    public function testCanAccessByAdminUser()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
        ));
        $currentUser->setPermissions(array('admin' => 1));
        $this->biz['user'] = $currentUser;

        $fireWall = new GroupFileFireWall($this->getBiz());
        $result = $fireWall->canAccess(array());
        $this->assertTrue($result);
    }

    public function testCanAccessByAuthorAndThread()
    {
        $attachment = $this->getAttachment();
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $this->biz['user'] = $currentUser;

        $this->mockBiz('Group:ThreadService', array(
            array(
                'functionName' => 'getThread',
                'returnValue' => array('id' => 111, 'userId' => $currentUser['id'], 'groupId' => 3),
            ),
        ));

        $this->mockBiz('Group:GroupService', array(
            array(
                'functionName' => 'getGroup',
                'withParams' => array(3),
                'returnValue' => array('id' => 111, 'ownerId' => $currentUser['id']),
            ),
        ));

        $fireWall = new GroupFileFireWall($this->getBiz());
        $result = $fireWall->canAccess($attachment);
        $this->assertTrue($result);
    }

    public function testCanAccessByAuthorAndThreadOrPost()
    {
        $attachment = array(
            'id' => 1,
            'type' => 'attachment',
            'fileId' => 3,
            'targetType' => 'group.post',
            'targetId' => 3,
        );
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $this->biz['user'] = $currentUser;

        $this->mockBiz('Group:ThreadService', array(
            array(
                'functionName' => 'getThread',
                'returnValue' => array('id' => 1, 'userId' => $currentUser['id'], 'groupId' => 3),
            ),
            array(
                'functionName' => 'getPost',
                'returnValue' => array('id' => 12, 'userId' => $currentUser['id'], 'threadId' => 5),
            ),
        ));

        $this->mockBiz('Group:GroupService', array(
            array(
                'functionName' => 'getGroup',
                'withParams' => array(3),
                'returnValue' => array('id' => 111, 'ownerId' => $currentUser['id']),
            ),
        ));

        $fireWall = new GroupFileFireWall($this->getBiz());
        $result = $fireWall->canAccess($attachment);
        $this->assertTrue($result);
    }

    public function testThreadNotCanAccess()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $this->biz['user'] = $currentUser;

        $attachment = $this->getAttachment();
        $this->mockBiz('Group:ThreadService', array(
            array(
                'functionName' => 'getThread',
                'returnValue' => array('id' => 111, 'userId' => 55, 'groupId' => 3),
            ),
        ));

        $this->mockBiz('Group:GroupService', array(
            array(
                'functionName' => 'getGroup',
                'withParams' => array(3),
                'returnValue' => array('id' => 111, 'ownerId' => 56),
            ),
        ));

        $fireWall = new GroupFileFireWall($this->getBiz());
        $result = $fireWall->canAccess($attachment);
        $this->assertFalse($result);
    }

    public function testPostNotCanAccess()
    {
        $attachment = array(
            'id' => 1,
            'type' => 'attachment',
            'fileId' => 3,
            'targetType' => 'group.post',
            'targetId' => 3,
        );

        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));

        $this->biz['user'] = $currentUser;

        $attachment = $this->getAttachment();
        $this->mockBiz('Group:ThreadService', array(
            array(
                'functionName' => 'getThread',
                'returnValue' => array('id' => 1, 'userId' => 55, 'groupId' => 3),
            ),
            array(
                'functionName' => 'getPost',
                'returnValue' => array('id' => 12, 'userId' => 65, 'threadId' => 5),
            ),
        ));

        $this->mockBiz('Group:GroupService', array(
            array(
                'functionName' => 'getGroup',
                'withParams' => array(3),
                'returnValue' => array('id' => 111, 'ownerId' => 56),
            ),
        ));

        $fireWall = new GroupFileFireWall($this->getBiz());
        $result = $fireWall->canAccess($attachment);
        $this->assertFalse($result);
    }

    private function getAttachment()
    {
        return array(
            'id' => 1,
            'type' => 'attachment',
            'fileId' => 3,
            'targetType' => 'group.thread',
            'targetId' => 3,
        );
    }
}
