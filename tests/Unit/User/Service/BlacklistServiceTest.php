<?php

namespace Tests\Unit\User\Service;

use Biz\BaseTestCase;
use Biz\User\CurrentUser;
use Biz\User\Service\BlacklistService;

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
     * @expectedException \Biz\User\BlacklistException
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

    public function testAddBlacklist()
    {
        $blacklist = array('userId' => 1, 'blackId' => 2);
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 1, 'nickname' => 'test'),
                    'withParams' => array(1),
                ),
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 2, 'nickname' => 'stu1'),
                    'withParams' => array(2),
                ),
            )
        );

        $result = $this->getBlacklistService()->addBlacklist($blacklist);

        $this->assertEquals(1, $result['userId']);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     * @expectedExceptionMessage exception.common_parameter_missing
     */
    public function testAddBlacklistWhenInvalidArgument()
    {
        $blacklist = array('userId' => 1);

        $this->getBlacklistService()->addBlacklist($blacklist);
    }

    /**
     * @expectedException \Biz\User\UserException
     * @expectedExceptionMessage exception.user.not_found
     */
    public function testAddBlacklistWithEmptyBlack()
    {
        $blacklist = array('userId' => 1, 'blackId' => 2);
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 1, 'nickname' => 'test'),
                    'withParams' => array(1),
                ),
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array(),
                    'withParams' => array(2),
                ),
            )
        );

        $this->getBlacklistService()->addBlacklist($blacklist);
    }

    /**
     * @expectedException \Biz\User\BlacklistException
     * @expectedExceptionMessage exception.blacklist.duplicate_add
     */
    public function testAddBlacklistWithDuplicateAdd()
    {
        $blacklist = array('userId' => 1, 'blackId' => 2);
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 1, 'nickname' => 'test'),
                    'withParams' => array(1),
                ),
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 2, 'nickname' => 'stu1'),
                    'withParams' => array(2),
                ),
            )
        );

        $this->mockBiz(
            'User:BlacklistDao',
            array(
                array(
                    'functionName' => 'getByUserIdAndBlackId',
                    'returnValue' => array('id' => 1),
                ),
            )
        );

        $this->getBlacklistService()->addBlacklist($blacklist);
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
     * @expectedException \Biz\User\BlacklistException
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
     * @expectedException \Biz\User\BlacklistException
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
     * @expectedException \Biz\User\UserException
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

    /**
     * @return BlacklistService
     */
    protected function getBlacklistService()
    {
        return $this->createService('User:BlacklistService');
    }
}
