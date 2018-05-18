<?php

namespace Tests\Unit\PostFilter\Service;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class TokenBucketServiceTest extends BaseTestCase
{
    public function testIncrToken()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('rules' => array('type' => array('ruleName1' => array(), 'ruleName2' => array('postNum' => 1)))),
                    'withParams' => array('post_num_rules'),
                ),
            )
        );
        $result = $this->getTokenBucketService()->incrToken('127.0.0.1', 'type');
        $this->assertNull($result);
    }

    public function testHasToken()
    {
        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('ipBlackList' => array(), 'rules' => array('type' => array('ruleName1' => array(), 'ruleName2' => array('postNum' => 1, 'interval' => time() + 5000)))),
                    'withParams' => array('post_num_rules'),
                    'runTimes' => 2,
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('ipBlackList' => array()),
                    'withParams' => array('post_num_rules'),
                    'runTimes' => 2,
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('ipBlackList' => array(), 'rules' => array()),
                    'withParams' => array('post_num_rules'),
                    'runTimes' => 2,
                ),
                array(
                    'functionName' => 'get',
                    'returnValue' => array('ipBlackList' => array('127.0.0.1')),
                    'withParams' => array('post_num_rules'),
                    'runTimes' => 1,
                ),
            )
        );
        $this->mockBiz(
            'PostFilter:RecentPostNumDao',
            array(
                array(
                    'functionName' => 'getByIpAndType',
                    'returnValue' => array('id' => 2, 'createdTime' => 5000, 'num' => 2),
                    'withParams' => array('127.0.0.1', 'type.ruleName2'),
                ),
            )
        );
        $result = $this->getTokenBucketService()->hasToken('127.0.0.1', 'type');
        $this->assertFalse($result);

        $result = $this->getTokenBucketService()->hasToken('127.0.0.1', 'type');
        $this->assertTrue($result);

        $result = $this->getTokenBucketService()->hasToken('127.0.0.1', 'type');
        $this->assertTrue($result);

        $result = $this->getTokenBucketService()->hasToken('127.0.0.1', 'type');
        $this->assertFalse($result);
    }

    public function testConfirmRule()
    {
        $this->mockBiz(
            'PostFilter:RecentPostNumDao',
            array(
                array(
                    'functionName' => 'getByIpAndType',
                    'returnValue' => array(),
                    'withParams' => array('127.0.0.1', 'type'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByIpAndType',
                    'returnValue' => array('id' => 2, 'createdTime' => 5000, 'num' => 1),
                    'withParams' => array('127.0.0.1', 'type'),
                    'runTimes' => 2,
                ),
                array(
                    'functionName' => 'delete',
                    'withParams' => array(2),
                ),
            )
        );
        $service = $this->getTokenBucketService();
        $result = ReflectionUtils::invokeMethod($service, 'confirmRule', array('127.0.0.1', 'type', array('interval' => 0, 'postNum' => 2)));
        $this->assertTrue($result);

        $result = ReflectionUtils::invokeMethod($service, 'confirmRule', array('127.0.0.1', 'type', array('interval' => 0, 'postNum' => 2)));
        $this->getRecentPostNumDao()->shouldHaveReceived('delete');
        $this->assertTrue($result);

        $result = ReflectionUtils::invokeMethod($service, 'confirmRule', array('127.0.0.1', 'type', array('interval' => time() + 5000, 'postNum' => 2)));
        $this->assertTrue($result);
    }

    public function testGetIpBlacklist()
    {
        $service = $this->getTokenBucketService();
        $result = ReflectionUtils::invokeMethod($service, 'getIpBlacklist');
        $this->assertEquals(array(), $result);
    }

    protected function getTokenBucketService()
    {
        return $this->createService('PostFilter:TokenBucketService');
    }

    protected function getRecentPostNumDao()
    {
        return $this->createDao('PostFilter:RecentPostNumDao');
    }
}
