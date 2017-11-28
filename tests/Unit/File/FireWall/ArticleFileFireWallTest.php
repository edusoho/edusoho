<?php

namespace Tests\Unit\File\FireWall;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\File\FireWall\ArticleFileFireWall;

class ArticleFileFireWallTest extends BaseTestCase
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
        $currentUser->setPermissions(array('admin' => 1));
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $fireWall = new ArticleFileFireWall($this->getBiz());
        $result1 = $fireWall->canAccess(array('targetId' => 111));
        $this->assertTrue($result1);

        $this->mockBiz(
            'Article:ArticleService',
            array(
                array(
                    'functionName' => 'getArticle',
                    'returnValue' => array('id' => 111, 'userId' => 2),
                    'withParams' => array(111),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getArticle',
                    'returnValue' => array('id' => 111, 'userId' => 3),
                    'withParams' => array(111),
                    'runTimes' => 1,
                ),
            )
        );
        $currentUser->setPermissions(array());
        $this->getServiceKernel()->setCurrentUser($currentUser);

        $result2 = $fireWall->canAccess(array('targetId' => 111));
        $result3 = $fireWall->canAccess(array('targetId' => 111));

        $this->assertTrue($result2);
        $this->assertFalse($result3);
    }
}