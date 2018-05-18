<?php

namespace Tests\Unit\Sensitive\Service;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;

class SensitiveServiceTest extends BaseTestCase
{
    public function testscanText()
    {
        $str2 = 'ｈｔｔｐ：／／ＪＢ５１．ｎｅｔ／　－　脚本之家';
        $msg = $this->getSensitiveService()->scanText($str2);
        $this->assertFalse($msg);
    }

    public function testBannedKeyword()
    {
        $service = $this->getSensitiveService();
        $this->mockBiz(
            'Sensitive:SensitiveDao',
            array(
                array(
                    'functionName' => 'findByState',
                    'returnValue' => array(array('id' => 2, 'name' => '测试')),
                    'withParams' => array('banned'),
                ),
                array(
                    'functionName' => 'getByName',
                    'returnValue' => array(),
                    'withParams' => array('测试'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByName',
                    'returnValue' => array('id' => 2, 'name' => '测试', 'state' => 'banned'),
                    'withParams' => array('测试'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'wave',
                    'withParams' => array(array(2), array('bannedNum' => 1)),
                ),
            )
        );
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 1, 'loginIp' => '127.0.0.1'),
                    'withParams' => array(1),
                ),
            )
        );
        $result = ReflectionUtils::invokeMethod($service, 'bannedKeyword', array('name', ''));
        $this->assertEquals(array('success' => false, 'text' => 'name'), $result);

        $result = ReflectionUtils::invokeMethod($service, 'bannedKeyword', array('测试的', ''));
        $this->assertEquals(array('success' => false, 'text' => '测试的'), $result);

        $result = ReflectionUtils::invokeMethod($service, 'bannedKeyword', array('测试的', ''));
        $this->getSensitiveDao()->shouldHaveReceived('wave');
        $this->assertEquals(array('success' => true, 'text' => '测试的'), $result);
    }

    public function testReplaceText()
    {
        $service = $this->getSensitiveService();
        $this->mockBiz(
            'Sensitive:SensitiveDao',
            array(
                array(
                    'functionName' => 'findByState',
                    'returnValue' => array(array('id' => 3, 'name' => '丑', 'state' => 'replaced')),
                    'withParams' => array('replaced'),
                ),
                array(
                    'functionName' => 'getByName',
                    'returnValue' => array('id' => 3, 'name' => '丑', 'state' => 'replaced'),
                    'withParams' => array('丑'),
                ),
                array(
                    'functionName' => 'wave',
                    'withParams' => array(array(3), array('bannedNum' => 1)),
                ),
            )
        );
        $this->mockBiz(
            'User:UserService',
            array(
                array(
                    'functionName' => 'getUser',
                    'returnValue' => array('id' => 1, 'loginIp' => '127.0.0.1'),
                    'withParams' => array(1),
                ),
            )
        );
        $result = ReflectionUtils::invokeMethod($service, 'replaceText', array('name', ''));
        $this->assertEquals('name', $result);

        $result = ReflectionUtils::invokeMethod($service, 'replaceText', array('丑的', ''));
        $this->getSensitiveDao()->shouldHaveReceived('wave');
        $this->assertEquals('*的', $result);
    }

    public function testscanTextWithRows()
    {
        $this->mockBiz(
            'Sensitive:SensitiveDao',
            array(
                array(
                    'functionName' => 'findAllKeywords',
                    'returnValue' => array(array('id' => 3, 'name' => '丑', 'state' => 'replaced')),
                    'withParams' => array(),
                ),
                array(
                    'functionName' => 'getByName',
                    'returnValue' => array(),
                    'withParams' => array('丑'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'getByName',
                    'returnValue' => array('id' => 3, 'name' => '丑', 'state' => 'replaced'),
                    'withParams' => array('丑'),
                    'runTimes' => 1,
                ),
                array(
                    'functionName' => 'wave',
                    'withParams' => array(array(3), array('bannedNum' => 1)),
                ),
            )
        );
        $result = $this->getSensitiveService()->scanText('test');
        $this->assertFalse($result);

        $result = $this->getSensitiveService()->scanText('丑的');
        $this->assertFalse($result);

        $result = $this->getSensitiveService()->scanText('丑的');
        $this->getSensitiveDao()->shouldHaveReceived('wave');
        $this->assertEquals('丑', $result);
    }

    public function testGetKeywordByName()
    {
        $this->mockBiz(
            'Sensitive:SensitiveDao',
            array(
                array(
                    'functionName' => 'getByName',
                    'returnValue' => array('id' => 3, 'name' => '丑', 'state' => 'replaced'),
                    'withParams' => array('丑'),
                ),
            )
        );
        $result = $this->getSensitiveService()->getKeywordByName('丑');
        $this->assertEquals(array('id' => 3, 'name' => '丑', 'state' => 'replaced'), $result);
    }

    public function testFindAllKeywords()
    {
        $this->mockBiz(
            'Sensitive:SensitiveDao',
            array(
                array(
                    'functionName' => 'findAllKeywords',
                    'returnValue' => array(array('id' => 3, 'name' => '丑', 'state' => 'replaced')),
                    'withParams' => array(),
                ),
            )
        );
        $result = $this->getSensitiveService()->findAllKeywords();
        $this->assertEquals(array(array('id' => 3, 'name' => '丑', 'state' => 'replaced')), $result);
    }

    public function testAddKeyword()
    {
        $result = $this->getSensitiveService()->addKeyword('丑', 'replaced');
        $this->assertEquals('丑', $result['name']);
    }

    public function testDeleteKeyword()
    {
        $this->mockBiz(
            'Sensitive:SensitiveDao',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array('id' => 3, 'name' => '丑', 'state' => 'replaced'),
                    'withParams' => array(3),
                ),
                array(
                    'functionName' => 'delete',
                    'returnValue' => 1,
                    'withParams' => array(3),
                ),
            )
        );
        $result = $this->getSensitiveService()->deleteKeyword(3);
        $this->assertEquals(1, $result);
    }

    public function testUpdateKeyword()
    {
        $this->mockBiz(
            'Sensitive:SensitiveDao',
            array(
                array(
                    'functionName' => 'update',
                    'returnValue' => 1,
                    'withParams' => array(3, array()),
                ),
            )
        );
        $result = $this->getSensitiveService()->updateKeyword(3, array());
        $this->assertEquals(1, $result);
    }

    public function testSearchkeywordsCount()
    {
        $this->mockBiz(
            'Sensitive:SensitiveDao',
            array(
                array(
                    'functionName' => 'count',
                    'returnValue' => 1,
                    'withParams' => array(array('id' => 2)),
                ),
            )
        );
        $result = $this->getSensitiveService()->searchkeywordsCount(array('id' => 2));
        $this->assertEquals(1, $result);
    }

    public function testSearchKeywords()
    {
        $this->mockBiz(
            'Sensitive:SensitiveDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 3, 'name' => '丑', 'state' => 'replaced')),
                    'withParams' => array(array('id' => 3), array(), 0, 5),
                ),
            )
        );
        $result = $this->getSensitiveService()->searchKeywords(array('id' => 3), array(), 0, 5);
        $this->assertEquals(array(array('id' => 3, 'name' => '丑', 'state' => 'replaced')), $result);
    }

    public function testSearchBanlogsCount()
    {
        $this->mockBiz(
            'Sensitive:KeywordBanlogDao',
            array(
                array(
                    'functionName' => 'count',
                    'returnValue' => 1,
                    'withParams' => array(array('id' => 2)),
                ),
            )
        );
        $result = $this->getSensitiveService()->searchBanlogsCount(array('id' => 2));
        $this->assertEquals(1, $result);
    }

    public function testSearchBanlogs()
    {
        $this->mockBiz(
            'Sensitive:KeywordBanlogDao',
            array(
                array(
                    'functionName' => 'search',
                    'returnValue' => array(array('id' => 3, 'keywordId' => 2, 'userId' => 2)),
                    'withParams' => array(array('id' => 3), array(), 0, 5),
                ),
            )
        );
        $result = $this->getSensitiveService()->searchBanlogs(array('id' => 3), array(), 0, 5);
        $this->assertEquals(array(array('id' => 3, 'keywordId' => 2, 'userId' => 2)), $result);
    }

    public function testSearchBanlogsByUserIds()
    {
        $this->mockBiz(
            'Sensitive:KeywordBanlogDao',
            array(
                array(
                    'functionName' => 'searchBanlogsByUserIds',
                    'returnValue' => array(array('id' => 3, 'keywordId' => 2, 'userId' => 2)),
                    'withParams' => array(array(2, 3), array(), 0, 5),
                ),
            )
        );
        $result = $this->getSensitiveService()->searchBanlogsByUserIds(array(2, 3), array(), 0, 5);
        $this->assertEquals(array(array('id' => 3, 'keywordId' => 2, 'userId' => 2)), $result);
    }

    public function testPlainTextFilter()
    {
        $params = array('测试name &nbsp; ', false);
        $result = ReflectionUtils::invokeMethod($this->getSensitiveService(), 'plainTextFilter', $params);
        $this->assertEquals($result, '测试name');

        $params = array('测试name &nbsp; ', true);
        $result = ReflectionUtils::invokeMethod($this->getSensitiveService(), 'plainTextFilter', $params);
        $this->assertEquals($result, '测试');
    }

    private function getSensitiveService()
    {
        return $this->createService('Sensitive:SensitiveService');
    }

    protected function getSensitiveDao()
    {
        return $this->createDao('Sensitive:SensitiveDao');
    }
}
