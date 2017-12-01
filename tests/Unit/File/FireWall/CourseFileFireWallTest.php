<?php

namespace Tests\Unit\File\FireWall;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\File\FireWall\CourseFileFireWall;

class CourseFileFireWallTest extends BaseTestCase
{
    public function testCanAccessByAdminUser()
    {
        $attachment = $this->getAttachment();
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
        $fireWall = new CourseFileFireWall($this->getBiz());
        $this->assertTrue($fireWall->canAccess($attachment));
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
        $fireWall = new CourseFileFireWall($this->getBiz());
        $this->mockBiz(
            'Course:ThreadService',
            array(
                array(
                    'functionName' => 'getThread',
                    'returnValue' => array('id' => 111, 'userId' => 2),
                    'withParams' => array(null, 3),
                ),
            )
        );

        $this->assertTrue($fireWall->canAccess($attachment));
    }

    public function testCanAccessByTeacherAndThread()
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
        $fireWall = new CourseFileFireWall($this->getBiz());

        $this->mockBiz(
            'Course:ThreadService',
            array(
                array(
                    'functionName' => 'getThread',
                    'returnValue' => array('id' => 111, 'userId' => 4, 'courseId' => 111),
                    'withParams' => array(null, 3),
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array('id' => 111, 'teacherIds' => array(1, 2)),
                    'withParams' => array(111),
                ),
            )
        );
        $this->assertTrue($fireWall->canAccess($attachment));
    }

    public function testNotAccessByThread()
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
        $fireWall = new CourseFileFireWall($this->getBiz());
        $this->mockBiz(
            'Course:ThreadService',
            array(
                array(
                    'functionName' => 'getThread',
                    'returnValue' => array('id' => 111, 'userId' => 1, 'courseId' => 111),
                    'withParams' => array(null, 3),
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array('id' => 111, 'teacherIds' => array(1, 3, 4)),
                    'withParams' => array(111),
                ),
            )
        );

        $this->assertNotTrue($fireWall->canAccess($attachment));
    }

    public function testCanAccessByPostAuthorAndPost()
    {
        $attachment = $this->getAttachment();
        $attachment['targetType'] = 'course.thread.post';
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
        $fireWall = new CourseFileFireWall($this->getBiz());
        $this->mockBiz(
            'Course:ThreadService',
            array(
                array(
                    'functionName' => 'getPost',
                    'returnValue' => array('id' => 111, 'userId' => 2),
                    'withParams' => array(null, 3),
                ),
            )
        );

        $this->assertTrue($fireWall->canAccess($attachment));
    }

    public function testCanAccessByThreadAuthorAndPost()
    {
        $attachment = $this->getAttachment();
        $attachment['targetType'] = 'course.thread.post';
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
        $fireWall = new CourseFileFireWall($this->getBiz());

        $this->mockBiz(
            'Course:ThreadService',
            array(
                array(
                    'functionName' => 'getPost',
                    'returnValue' => array('id' => 111, 'userId' => 5, 'courseId' => 111, 'threadId' => 5),
                    'withParams' => array(null, 3),
                ),
                array(
                    'functionName' => 'getThread',
                    'returnValue' => array('id' => 111, 'userId' => 2, 'courseId' => 111),
                    'withParams' => array(null, 5),
                ),
            )
        );

        $this->assertTrue($fireWall->canAccess($attachment));
    }

    public function testCanAccessByTeacherAndPost()
    {
        $attachment = $this->getAttachment();
        $attachment['targetType'] = 'course.thread.post';
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
        $fireWall = new CourseFileFireWall($this->getBiz());

        $this->mockBiz(
            'Course:ThreadService',
            array(
                array(
                    'functionName' => 'getPost',
                    'returnValue' => array('id' => 111, 'userId' => 20, 'courseId' => 111, 'threadId' => 5),
                    'withParams' => array(null, 3),
                ),
                array(
                    'functionName' => 'getThread',
                    'returnValue' => array('id' => 111, 'userId' => 50, 'courseId' => 111),
                    'withParams' => array(null, 5),
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array('id' => 111, 'teacherIds' => array(1, 2)),
                    'withParams' => array(111),
                ),
            )
        );

        $this->assertTrue($fireWall->canAccess($attachment));
    }

    public function testCanNotAccessByTeacherAndPost()
    {
        $attachment = $this->getAttachment();
        $attachment['targetType'] = 'course.thread.post';
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
        $fireWall = new CourseFileFireWall($this->getBiz());

        $this->mockBiz(
            'Course:ThreadService',
            array(
                array(
                    'functionName' => 'getPost',
                    'returnValue' => array('id' => 111, 'userId' => 20, 'courseId' => 111, 'threadId' => 5),
                    'withParams' => array(null, 3),
                ),
                array(
                    'functionName' => 'getThread',
                    'returnValue' => array('id' => 111, 'userId' => 50, 'courseId' => 111),
                    'withParams' => array(null, 5),
                ),
            )
        );
        $this->mockBiz(
            'Course:CourseService',
            array(
                array(
                    'functionName' => 'getCourse',
                    'returnValue' => array('id' => 111, 'teacherIds' => array(1)),
                    'withParams' => array(111),
                ),
            )
        );

        $this->assertNotTrue($fireWall->canAccess($attachment));
    }

    private function getAttachment()
    {
        return array(
            'id' => 1,
            'type' => 'attachment',
            'fileId' => 3,
            'targetType' => 'course.thread',
            'targetId' => 3,
        );
    }
}
