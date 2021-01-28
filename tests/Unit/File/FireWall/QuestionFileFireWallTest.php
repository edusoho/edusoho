<?php

namespace Tests\Unit\File\FireWall;

use Biz\BaseTestCase;
use Biz\File\FireWall\QuestionFileFireWall;
use Biz\User\CurrentUser;

class QuestionFileFireWallTest extends BaseTestCase
{
    public function testCanAccessByAdminUser()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray([
            'id' => 2,
            'nickname' => 'admin',
            'email' => 'admin@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => ['ROLE_USER', 'ROLE_ADMIN'],
        ]);
        $currentUser->setPermissions(['admin' => 1]);
        $this->biz['user'] = $currentUser;

        $fireWall = new QuestionFileFireWall($this->getBiz());
        $result = $fireWall->canAccess([]);
        $this->assertTrue($result);
    }

    private function getAttachment()
    {
        return [
            'id' => 1,
            'type' => 'attachment',
            'fileId' => 3,
            'targetType' => 'question.answer',
            'targetId' => 3,
        ];
    }
}
