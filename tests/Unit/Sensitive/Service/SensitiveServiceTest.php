<?php

namespace Tests\Unit\Sensitive\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;

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
            [
                [
                    'functionName' => 'findByState',
                    'returnValue' => [['id' => 2, 'name' => '测试']],
                    'withParams' => ['banned'],
                ],
                [
                    'functionName' => 'getByName',
                    'returnValue' => [],
                    'withParams' => ['测试'],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'getByName',
                    'returnValue' => ['id' => 2, 'name' => '测试', 'state' => 'banned'],
                    'withParams' => ['测试'],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'wave',
                    'withParams' => [[2], ['bannedNum' => 1]],
                ],
            ]
        );
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'getUser',
                    'returnValue' => ['id' => 1, 'loginIp' => '127.0.0.1'],
                    'withParams' => [1],
                ],
            ]
        );
        $result = ReflectionUtils::invokeMethod($service, 'bannedKeyword', ['name', '']);
        $this->assertEquals(['success' => false, 'text' => 'name'], $result);

        $result = ReflectionUtils::invokeMethod($service, 'bannedKeyword', ['测试的', '']);
        $this->assertEquals(['success' => false, 'text' => '测试的'], $result);

        $result = ReflectionUtils::invokeMethod($service, 'bannedKeyword', ['测试的', '']);
        $this->getSensitiveDao()->shouldHaveReceived('wave');
        $this->assertEquals(['success' => true, 'text' => '测试的'], $result);
    }

    public function testReplaceText()
    {
        $service = $this->getSensitiveService();
        $this->mockBiz(
            'Sensitive:SensitiveDao',
            [
                [
                    'functionName' => 'findByState',
                    'returnValue' => [['id' => 3, 'name' => '丑', 'state' => 'replaced']],
                    'withParams' => ['replaced'],
                ],
                [
                    'functionName' => 'getByName',
                    'returnValue' => ['id' => 3, 'name' => '丑', 'state' => 'replaced'],
                    'withParams' => ['丑'],
                ],
                [
                    'functionName' => 'wave',
                    'withParams' => [[3], ['bannedNum' => 1]],
                ],
            ]
        );
        $this->mockBiz(
            'User:UserService',
            [
                [
                    'functionName' => 'getUser',
                    'returnValue' => ['id' => 1, 'loginIp' => '127.0.0.1'],
                    'withParams' => [1],
                ],
            ]
        );
        $result = ReflectionUtils::invokeMethod($service, 'handleContent', ['name', '']);
        $this->assertEquals('name', $result['content']);

        $result = ReflectionUtils::invokeMethod($service, 'handleContent', ['丑的', '']);
        $this->getSensitiveDao()->shouldHaveReceived('wave');
        $this->assertEquals('*的', $result['content']);
    }

    public function testscanTextWithRows()
    {
        $this->mockBiz(
            'Sensitive:SensitiveDao',
            [
                [
                    'functionName' => 'findAllKeywords',
                    'returnValue' => [['id' => 3, 'name' => '丑', 'state' => 'replaced']],
                    'withParams' => [],
                ],
                [
                    'functionName' => 'getByName',
                    'returnValue' => [],
                    'withParams' => ['丑'],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'getByName',
                    'returnValue' => ['id' => 3, 'name' => '丑', 'state' => 'replaced'],
                    'withParams' => ['丑'],
                    'runTimes' => 1,
                ],
                [
                    'functionName' => 'wave',
                    'withParams' => [[3], ['bannedNum' => 1]],
                ],
            ]
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
            [
                [
                    'functionName' => 'getByName',
                    'returnValue' => ['id' => 3, 'name' => '丑', 'state' => 'replaced'],
                    'withParams' => ['丑'],
                ],
            ]
        );
        $result = $this->getSensitiveService()->getKeywordByName('丑');
        $this->assertEquals(['id' => 3, 'name' => '丑', 'state' => 'replaced'], $result);
    }

    public function testFindAllKeywords()
    {
        $this->mockBiz(
            'Sensitive:SensitiveDao',
            [
                [
                    'functionName' => 'findAllKeywords',
                    'returnValue' => [['id' => 3, 'name' => '丑', 'state' => 'replaced']],
                    'withParams' => [],
                ],
            ]
        );
        $result = $this->getSensitiveService()->findAllKeywords();
        $this->assertEquals([['id' => 3, 'name' => '丑', 'state' => 'replaced']], $result);
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
            [
                [
                    'functionName' => 'get',
                    'returnValue' => ['id' => 3, 'name' => '丑', 'state' => 'replaced'],
                    'withParams' => [3],
                ],
                [
                    'functionName' => 'delete',
                    'returnValue' => 1,
                    'withParams' => [3],
                ],
            ]
        );
        $result = $this->getSensitiveService()->deleteKeyword(3);
        $this->assertEquals(1, $result);
    }

    public function testUpdateKeyword()
    {
        $this->mockBiz(
            'Sensitive:SensitiveDao',
            [
                [
                    'functionName' => 'update',
                    'returnValue' => 1,
                    'withParams' => [3, []],
                ],
            ]
        );
        $result = $this->getSensitiveService()->updateKeyword(3, []);
        $this->assertEquals(1, $result);
    }

    public function testSearchkeywordsCount()
    {
        $this->mockBiz(
            'Sensitive:SensitiveDao',
            [
                [
                    'functionName' => 'count',
                    'returnValue' => 1,
                    'withParams' => [['id' => 2]],
                ],
            ]
        );
        $result = $this->getSensitiveService()->searchkeywordsCount(['id' => 2]);
        $this->assertEquals(1, $result);
    }

    public function testSearchKeywords()
    {
        $this->mockBiz(
            'Sensitive:SensitiveDao',
            [
                [
                    'functionName' => 'search',
                    'returnValue' => [['id' => 3, 'name' => '丑', 'state' => 'replaced']],
                    'withParams' => [['id' => 3], [], 0, 5],
                ],
            ]
        );
        $result = $this->getSensitiveService()->searchKeywords(['id' => 3], [], 0, 5);
        $this->assertEquals([['id' => 3, 'name' => '丑', 'state' => 'replaced']], $result);
    }

    public function testSearchBanlogsCount()
    {
        $this->mockBiz(
            'Sensitive:KeywordBanlogDao',
            [
                [
                    'functionName' => 'count',
                    'returnValue' => 1,
                    'withParams' => [['id' => 2]],
                ],
            ]
        );
        $result = $this->getSensitiveService()->searchBanlogsCount(['id' => 2]);
        $this->assertEquals(1, $result);
    }

    public function testSearchBanlogs()
    {
        $this->mockBiz(
            'Sensitive:KeywordBanlogDao',
            [
                [
                    'functionName' => 'search',
                    'returnValue' => [['id' => 3, 'keywordId' => 2, 'userId' => 2]],
                    'withParams' => [['id' => 3], [], 0, 5],
                ],
            ]
        );
        $result = $this->getSensitiveService()->searchBanlogs(['id' => 3], [], 0, 5);
        $this->assertEquals([['id' => 3, 'keywordId' => 2, 'userId' => 2]], $result);
    }

    public function testSearchBanlogsByUserIds()
    {
        $this->mockBiz(
            'Sensitive:KeywordBanlogDao',
            [
                [
                    'functionName' => 'searchBanlogsByUserIds',
                    'returnValue' => [['id' => 3, 'keywordId' => 2, 'userId' => 2]],
                    'withParams' => [[2, 3], [], 0, 5],
                ],
            ]
        );
        $result = $this->getSensitiveService()->searchBanlogsByUserIds([2, 3], [], 0, 5);
        $this->assertEquals([['id' => 3, 'keywordId' => 2, 'userId' => 2]], $result);
    }

    public function testPlainTextFilter()
    {
        $params = ['测试name &nbsp; ', false];
        $result = ReflectionUtils::invokeMethod($this->getSensitiveService(), 'plainTextFilter', $params);
        $this->assertEquals($result, '测试name');

        $params = ['测试name &nbsp; ', true];
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
