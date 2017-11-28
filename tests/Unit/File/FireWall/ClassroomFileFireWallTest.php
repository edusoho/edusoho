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
            'id' => 3,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER', 'ROLE_ADMIN'),
        ));
        $currentUser->setPermissions(array('admin' => 1));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $fireWall = new ClassroomFileFireWall($this->getBiz());
        $result1 = $fireWall->canAccess(array());
        $this->assertTrue($result1);

        $this->mockBiz(
            'Thread:ThreadService',
            array(
                array(
                    'functionName' => 'getThread',
                    'returnValue' => array('id' => 111, 'userId' => 3),
                    'withParams' => array(111),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getThread',
                    'returnValue' => array('id' => 111, 'userId' => 4, 'targetId' => 111),
                    'withParams' => array(111),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getThread',
                    'returnValue' => array('id' => 111, 'userId' => 3),
                    'withParams' => array(111),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getThread',
                    'returnValue' => array('id' => 111, 'userId' => 4, 'targetId' => 111),
                    'withParams' => array(111),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getPost',
                    'returnValue' => array('id' => 111, 'userId' => 3),
                    'withParams' => array(111),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getPost',
                    'returnValue' => array('id' => 111, 'userId' => 4, 'threadId' => 111),
                    'withParams' => array(111),
                    'runTimes' => 2,
                ),
            )
        );
        $this->mockBiz(
            'Classroom:ClassroomService',
            array(
                array(
                    'functionName' => 'getClassroom',
                    'returnValue' => array('id' => 111, 'headTeacherId' => 3, 'teacherIds' => array(1, 2, 3, 4)),
                    'withParams' => array(111),
                ),
            )
        );
        $currentUser->setPermissions(array());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $result2 = $fireWall->canAccess(array('targetType' => 'thread', 'targetId' => 111));
        $this->assertTrue($result2);

        $result3 = $fireWall->canAccess(array('targetType' => 'thread', 'targetId' => 111));
        $this->assertTrue($result3);

        $result4 = $fireWall->canAccess(array('targetType' => 'post', 'targetId' => 111));
        $this->assertTrue($result4);

        $result5 = $fireWall->canAccess(array('targetType' => 'post', 'targetId' => 111));
        $this->assertTrue($result5);

        $result6 = $fireWall->canAccess(array('targetType' => 'post', 'targetId' => 111));
        $this->assertTrue($result6);

        $result7 = $fireWall->canAccess(array('targetType' => '', 'targetId' => 111));
        $this->assertFalse($result7);
    }
}
