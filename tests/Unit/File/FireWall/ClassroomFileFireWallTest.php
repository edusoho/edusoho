<?php

namespace Tests\Unit\File\FireWall;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\File\FireWall\ClassroomFileFireWall;

class ClassroomFileFireWallTest extends BaseTestCase
{
    public function testCanAccess()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $fireWall = new ClassroomFileFireWall($this->getBiz());
        $result = $fireWall->canAccess(array('targetType' => '', 'targetId' => 111));
        $this->assertFalse($result);
    }

    public function testCanAccessWithAdminUser()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
        ));
        $currentUser->setPermissions(array('admin' => 1));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $fireWall = new ClassroomFileFireWall($this->getBiz());
        $result = $fireWall->canAccess(array());
        $this->assertTrue($result);
    }

    public function testCanAccessWithThreadCreaterAndThreadType()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $fireWall = new ClassroomFileFireWall($this->getBiz());
        $this->mockBiz(
            'Thread:ThreadService',
            array(
                array(
                    'functionName' => 'getThread',
                    'returnValue' => array('id' => 111, 'userId' => 2),
                    'withParams' => array(111),
                ),
            )
        );

        $result = $fireWall->canAccess(array('targetType' => 'thread', 'targetId' => 111));
        $this->assertTrue($result);
    }

    public function testCanAccessWithTeacherAndThreadType()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $fireWall = new ClassroomFileFireWall($this->getBiz());
        $this->mockBiz(
            'Thread:ThreadService',
            array(
                array(
                    'functionName' => 'getThread',
                    'returnValue' => array('id' => 111, 'userId' => 3, 'targetId' => 111),
                    'withParams' => array(111),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomService',
            array(
                array(
                    'functionName' => 'getClassroom',
                    'returnValue' => array('id' => 111, 'headTeacherId' => 1, 'teacherIds' => array(1, 2, 3)),
                    'withParams' => array(111),
                ),
            )
        );

        $result = $fireWall->canAccess(array('targetType' => 'thread', 'targetId' => 111));
        $this->assertTrue($result);
    }

    public function testCanAccessWithPostCreaterAndPostType()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $fireWall = new ClassroomFileFireWall($this->getBiz());

        $this->mockBiz(
            'Thread:ThreadService',
            array(
                array(
                    'functionName' => 'getPost',
                    'returnValue' => array('id' => 111, 'userId' => 2),
                    'withParams' => array(111),
                ),
            )
        );

        $result = $fireWall->canAccess(array('targetType' => 'post', 'targetId' => 111));
        $this->assertTrue($result);
    }

    public function testCanAccessWithThreadCreaterAndPostType()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $fireWall = new ClassroomFileFireWall($this->getBiz());
        $this->mockBiz(
            'Thread:ThreadService',
            array(
                array(
                    'functionName' => 'getThread',
                    'returnValue' => array('id' => 111, 'userId' => 2),
                    'withParams' => array(111),
                ),
                array(
                    'functionName' => 'getPost',
                    'returnValue' => array('id' => 111, 'userId' => 3, 'threadId' => 111),
                    'withParams' => array(111),
                ),
            )
        );

        $result = $fireWall->canAccess(array('targetType' => 'post', 'targetId' => 111));
        $this->assertTrue($result);
    }

    public function testCanAccessWithTeacherAndPostType()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 2,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $fireWall = new ClassroomFileFireWall($this->getBiz());
        $this->mockBiz(
            'Thread:ThreadService',
            array(
                array(
                    'functionName' => 'getThread',
                    'returnValue' => array('id' => 111, 'userId' => 3, 'targetId' => 111),
                    'withParams' => array(111),
                ),
                array(
                    'functionName' => 'getPost',
                    'returnValue' => array('id' => 111, 'userId' => 3, 'threadId' => 111),
                    'withParams' => array(111),
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomService',
            array(
                array(
                    'functionName' => 'getClassroom',
                    'returnValue' => array('id' => 111, 'headTeacherId' => 1, 'teacherIds' => array(1, 2, 3)),
                    'withParams' => array(111),
                ),
            )
        );

        $result = $fireWall->canAccess(array('targetType' => 'post', 'targetId' => 111));
        $this->assertTrue($result);
    }
}
