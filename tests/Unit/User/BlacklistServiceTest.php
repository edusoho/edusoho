<?php

namespace Tests\Unit\User;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;

class BlacklistServiceTest extends BaseTestCase
{
    public function testGetBlacklist()
    {
        $this->mockBiz(
            'User:BlacklistDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 2, 'userId' => 3, 'blackId' => 3),
                    'withParams' => array(2),
                ),
            )
        );
        $result = $this->getBlacklistService()->getBlacklist(2);
        $this->assertEquals(array('id' => 2, 'userId' => 3, 'blackId' => 3), $result);
    }

    public function testGetBlacklistByUserIdAndBlackId()
    {
        $this->mockBiz(
            'User:BlacklistDao',
            array(
                array(
                    'functionName' => 'getByUserIdAndBlackId',
                    'returnValue' => array('id' => 2, 'userId' => 3, 'blackId' => 4),
                    'withParams' => array(3, 4),
                ),
            )
        );
        $result = $this->getBlacklistService()->getBlacklistByUserIdAndBlackId(3, 4);
        $this->assertEquals(array('id' => 2, 'userId' => 3, 'blackId' => 4), $result);
    }

    public function testFindBlacklistsByUserId()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 1, 'nickname' => 'test'),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'User:BlacklistDao',
            array(
                array(
                    'functionName' => 'findByUserId',
                    'returnValue' => array('id' => 2, 'userId' => 1, 'blackId' => 4),
                    'withParams' => array(1),
                ),
            )
        );
        $result = $this->getBlacklistService()->findBlacklistsByUserId(1);
        $this->assertEquals(array('id' => 2, 'userId' => 1, 'blackId' => 4), $result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testFindBlacklistsByUserIdWithWrongUserId()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 3,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 2, 'nickname' => 'test'),
                    'withParams' => array(2),
                ),
            )
        );
        $this->getBlacklistService()->findBlacklistsByUserId(2);
    }

    public function testDeleteBlacklistByUserIdAndBlackId()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 1, 'nickname' => 'test'),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'User:BlacklistDao',
            array(
                array(
                    'functionName' => 'getByUserIdAndBlackId',
                    'returnValue' => array('id' => 2, 'userId' => 1, 'blackId' => 4),
                    'withParams' => array(1, 4),
                ),
                array(
                    'functionName' => 'deleteByUserIdAndBlackId',
                    'returnValue' => 1,
                    'withParams' => array(1, 4),
                ),
            )
        );
        $result = $this->getBlacklistService()->deleteBlacklistByUserIdAndBlackId(1, 4);
        $this->assertEquals(1, $result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\AccessDeniedException
     */
    public function testDeleteBlacklistByUserIdAndBlackIdWithWrongUserId()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 3,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 2, 'nickname' => 'test'),
                    'withParams' => array(2),
                ),
            )
        );
        $this->getBlacklistService()->deleteBlacklistByUserIdAndBlackId(2, 4);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testDeleteBlacklistByUserIdAndBlackIdWithEmptyBlack()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 1, 'nickname' => 'test'),
                    'withParams' => array(1),
                ),
            )
        );
        $this->mockBiz(
            'User:BlacklistDao',
            array(
                array(
                    'functionName' => 'getByUserIdAndBlackId',
                    'returnValue' => array(),
                    'withParams' => array(1, 4),
                ),
            )
        );
        $this->getBlacklistService()->deleteBlacklistByUserIdAndBlackId(1, 4);
    }

    public function testCanTakeBlacklist()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 1, 'nickname' => 'test'),
                    'withParams' => array(1),
                ),
            )
        );
        $result = $this->getBlacklistService()->canTakeBlacklist(1);
        $this->assertTrue($result);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\NotFoundException
     */
    public function testCanTakeBlacklistWithEmptyOwnerId()
    {
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => '', 'nickname' => 'test'),
                    'withParams' => array(1),
                ),
            )
        );
        $result = $this->getBlacklistService()->canTakeBlacklist(1);
    }

    public function testCanTakeBlacklistWithWrongUserId()
    {
        $currentUser = new CurrentUser();
        $currentUser->fromArray(array(
            'id' => 3,
            'nickname' => 'admin1',
            'email' => 'admin3@admin.com',
            'password' => 'admin',
            'currentIp' => '127.0.0.1',
            'roles' => array('ROLE_USER'),
        ));
        $this->getServiceKernel()->setCurrentUser($currentUser);
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 2, 'nickname' => 'test'),
                    'withParams' => array(2),
                ),
            )
        );
        $result = $this->getBlacklistService()->canTakeBlacklist(2);
        $this->assertFalse($result);
    }

    protected function getBlacklistService()
    {
        return $this->createService('User:BlacklistService');
    }
}
