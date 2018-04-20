<?php

namespace Tests\Unit\File\FireWall;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\File\FireWall\QuestionFileFireWall;

class QuestionFileFireWallTest extends BaseTestCase
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

        $fireWall = new QuestionFileFireWall($this->getBiz());
        $result = $fireWall->canAccess(array());
        $this->assertTrue($result);
    }

    public function testCanAccessQuestionAnswer()
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

        $fireWall = new QuestionFileFireWall($this->getBiz());
        $result = $fireWall->canAccess($attachment);
        $this->assertFalse($result);

        $this->mockBiz('Testpaper:TestpaperService', array(
            array(
                'functionName' => 'getItemResult',
                'returnValue' => array('id' => 123, 'testId' => 1, 'questionId' => 3),
            ),
        ));

        $fireWall = new QuestionFileFireWall($this->getBiz());
        $result = $fireWall->canAccess($attachment);
        $this->assertTrue($result);
    }

    public function testCanAccessQuestion()
    {
        $attachment = array(
            'id' => 1,
            'type' => 'attachment',
            'fileId' => 3,
            'targetType' => 'question.stem',
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

        $fireWall = new QuestionFileFireWall($this->getBiz());
        $result = $fireWall->canAccess($attachment);
        $this->assertFalse($result);
        

        $this->mockBiz('Question:QuestionService', array(
            array(
                'functionName' => 'get',
                'withParams' => array(3),
                'returnValue' => array('id' => 3, 'userId' => $currentUser['id']),
            ),
            array(
                'functionName' => 'get',
                'withParams' => array(4),
                'returnValue' => array('id' => 4, 'userId' => 5),
            ),
        ));

        $fireWall = new QuestionFileFireWall($this->getBiz());
        $result = $fireWall->canAccess($attachment);
        $this->assertTrue($result);

        $attachment = array(
            'id' => 1,
            'type' => 'attachment',
            'fileId' => 3,
            'targetType' => 'question.stem',
            'targetId' => 4,
        );
        $fireWall = new QuestionFileFireWall($this->getBiz());
        $result = $fireWall->canAccess($attachment);
        $this->assertFalse($result);
    }

    private function getAttachment()
    {
        return array(
            'id' => 1,
            'type' => 'attachment',
            'fileId' => 3,
            'targetType' => 'question.answer',
            'targetId' => 3,
        );
    }
}
