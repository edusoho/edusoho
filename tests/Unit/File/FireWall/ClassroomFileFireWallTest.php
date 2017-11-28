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
        $classroomFileFireWall = new ClassroomFileFireWall($this->getBiz());
        $result1 = $classroomFileFireWall->canAccess(array());
        $this->assertTrue($result1);

        $currentUser->setPermissions(array());
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $thread1 = $this->createThread(3);
        $thread2 = $this->createThread(4);
        $classroom = $this->createClassroom();
        $threadPost1 = $this->createThreadPost(3, 1);
        $threadPost2 = $this->createThreadPost(4, 2);

        $result2 = $classroomFileFireWall->canAccess(array('targetType' => 'thread', 'targetId' => 1));
        $this->assertTrue($result2);

        $result3 = $classroomFileFireWall->canAccess(array('targetType' => 'thread', 'targetId' => 2));
        $this->assertTrue($result3);

        $result4 = $classroomFileFireWall->canAccess(array('targetType' => 'post', 'targetId' => 1));
        $this->assertTrue($result4);

        $result5 = $classroomFileFireWall->canAccess(array('targetType' => 'post', 'targetId' => 1));
        $this->assertTrue($result5);

        $result6 = $classroomFileFireWall->canAccess(array('targetType' => 'post', 'targetId' => 2));
        $this->assertTrue($result6);

        $result7 = $classroomFileFireWall->canAccess(array('targetType' => '', 'targetId' => 1));
        $this->assertFalse($result7);

        $currentUser->setPermissions(array('admin' => 1));
        $this->getServiceKernel()->setCurrentUser($currentUser);
    }

    protected function createThread($userId)
    {
        $fields = array(
            'title' => 'title',
            'userId' => $userId,
            'targetId' => 1,
        );
        return $this->getThreadDao()->create($fields);
    }

    protected function createClassroom()
    {
        $fields = array(
            'title' => 'title',
            'headTeacherId' => 0,
            'teacherIds' => array(1, 2, 3, 4),
        );
        return $this->getClassroomDao()->create($fields);
    }

    protected function createThreadPost($userId, $threadId)
    {
        $fields = array(
            'userId' => $userId,
            'content' => 'content',
            'targetId' => 1,
            'threadId' => $threadId,
        );
        return $this->getThreadPostDao()->create($fields);
    }

    protected function getThreadDao()
    {
        return $this->createDao('Thread:ThreadDao');
    }

    protected function getClassroomDao()
    {
        return $this->createDao('Classroom:ClassroomDao');
    }

    protected function getThreadPostDao()
    {
        return $this->createDao('Thread:ThreadPostDao');
    }
}
