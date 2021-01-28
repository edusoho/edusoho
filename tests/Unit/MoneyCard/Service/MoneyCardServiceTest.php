<?php

namespace Tests\Unit\MoneyCard\Service;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;

class MoneyCardServiceTest extends BaseTestCase
{
    public function testGetMoneyCard()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', [
           ['functionName' => 'get', 'withParams' => [1990, ['lock' => true]], 'returnValue' => $this->getFakeMoneyCard()],
        ]);
        $moneyCard = $this->getMoneyCardService()->getMoneyCard(1990, true);
        $this->assertEquals($this->getFakeMoneyCard(), $moneyCard);
    }

    public function testGetMoneyCardByIds()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'getMoneyCardByIds', 'withParams' => [[1, 2, 3]], 'returnValue' => $this->getFakeMoneyCard()],
        ]);
        $moneyCard = $this->getMoneyCardService()->getMoneyCardByIds([1, 2, 3]);
        $this->assertEquals($this->getFakeMoneyCard(), $moneyCard);
    }

    public function testGetMoneyCardByPassword()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'getMoneyCardByPassword', 'withParams' => [1234567], 'returnValue' => $this->getFakeMoneyCard()],
        ]);
        $moneyCard = $this->getMoneyCardService()->getMoneyCardByPassword(1234567);
        $this->assertEquals($this->getFakeMoneyCard(), $moneyCard);
    }

    public function testGetBatch()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'get', 'withParams' => [1], 'returnValue' => $this->getFakeMoneyCardBatch()],
        ]);
        $batch = $this->getMoneyCardService()->getBatch(1);
        $this->assertEquals($this->getFakeMoneyCardBatch(), $batch);
    }

    public function testSearchMoneyCards()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'search', 'withParams' => [[], [], 0, 1], 'returnValue' => $this->getFakeMoneyCard()],
        ]);

        $moneyCard = $this->getMoneyCardService()->searchMoneyCards([], [], 0, 1);
        $this->assertEquals($this->getFakeMoneyCard(), $moneyCard);
    }

    public function testCountMoneyCards()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'count', 'withParams' => [[]], 'returnValue' => 100],
        ]);

        $count = $this->getMoneyCardService()->countMoneyCards([]);
        $this->assertEquals(100, $count);
    }

    public function testSearchBatches()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'search', 'withParams' => [[], [], 0, 1], 'returnValue' => $this->getFakeMoneyCardBatch()],
        ]);
        $batch = $this->getMoneyCardService()->searchBatches([], [], 0, 1);
        $this->assertEquals($this->getFakeMoneyCardBatch(), $batch);
    }

    public function testCountBatches()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'count', 'withParams' => [[]], 'returnValue' => 100],
        ]);
        $count = $this->getMoneyCardService()->countBatches([]);
        $this->assertEquals(100, $count);
    }

    /**
     * @expectedException \Biz\MoneyCard\MoneyCardException
     */
    public function testCreateMoneyCardWithError1()
    {
        $this->getMoneyCardService()->createMoneyCard(['money' => -100]);
    }

    /**
     * @expectedException \Biz\MoneyCard\MoneyCardException
     */
    public function testCreateMoneyCardWithError2()
    {
        $this->getMoneyCardService()->createMoneyCard(['coin' => -100]);
    }

    /**
     * @expectedException \Biz\MoneyCard\MoneyCardException
     */
    public function testCreateMoneyCardWithError3()
    {
        $this->getMoneyCardService()->createMoneyCard(['cardLength' => -100]);
    }

    /**
     * @expectedException \Biz\MoneyCard\MoneyCardException
     */
    public function testCreateMoneyCardWithError4()
    {
        $this->getMoneyCardService()->createMoneyCard(['number' => -100]);
    }

    /**
     * @expectedException \Biz\MoneyCard\MoneyCardException
     */
    public function testCreateMoneyCardWithError5()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'isCardIdAvailable', 'returnValue' => false],
            ['functionName' => 'getMoneyCardByPassword', 'returnValue' => null],
        ]);

        $this->getMoneyCardService()->createMoneyCard([
            'cardLength' => 8,
            'number' => 123,
            'cardPrefix' => 'pre',
            'passwordLength' => 6,
            'deadline' => time(),
        ]);
    }

    public function testCreateMoneyCardSuccess()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'isCardIdAvailable', 'returnValue' => true],
            ['functionName' => 'getMoneyCardByPassword', 'returnValue' => null],
            ['functionName' => 'create', 'returnValue' => $this->getFakeMoneyCard()],
        ]);

        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'create', 'returnValue' => $this->getFakeMoneyCardBatch()],
        ]);

        $batch = $this->getMoneyCardService()->createMoneyCard([
            'cardLength' => 8,
            'number' => 123,
            'cardPrefix' => 'pre',
            'passwordLength' => 6,
            'deadline' => date('Y-m-d', time()),
        ]);

        $this->assertEquals($this->getFakeMoneyCardBatch(), $batch);
    }

    /**
     * @expectedException \Biz\MoneyCard\MoneyCardException
     */
    public function testLockMoneyCardWithEmptyCard()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'get', 'returnValue' => null],
        ]);

        $this->getMoneyCardService()->lockMoneyCard(1);
    }

    /**
     * @expectedException \Biz\MoneyCard\MoneyCardException
     */
    public function testLockMoneyCardWithBadStatus()
    {
        $fakeCard = $this->getFakeMoneyCard();
        $fakeCard['cardStatus'] = 'bad';
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'get', 'returnValue' => $fakeCard],
        ]);

        $this->getMoneyCardService()->lockMoneyCard(1);
    }

    public function testLockMoneyCardSuccess()
    {
        $fakeCard = $this->getFakeMoneyCard();
        $fakeCard['cardStatus'] = 'receive';

        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'get', 'returnValue' => $fakeCard],
            ['functionName' => 'update', 'withParams' => [$fakeCard['id'], ['cardStatus' => 'invalid']], 'returnValue' => $fakeCard],
        ]);

        $this->mockBiz('Card:CardService', [
            ['functionName' => 'getCardByCardIdAndCardType', 'withParams' => [$fakeCard['id'], 'moneyCard'], 'returnValue' => ['userId' => 1]],
            ['functionName' => 'updateCardByCardIdAndCardType', 'withParams' => [$fakeCard['id'], 'moneyCard', ['status' => 'invalid']]],
        ]);

        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'get', 'withParams' => [$fakeCard['id']], 'returnValue' => $this->getFakeMoneyCardBatch()],
        ]);

        $this->getMoneyCardService()->lockMoneyCard($fakeCard['id']);
    }

    /**
     * @expectedException \Biz\MoneyCard\MoneyCardException
     */
    public function testUnlockMoneyCardWithEmpty()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'get', 'returnValue' => null],
        ]);

        $this->getMoneyCardService()->unlockMoneyCard(1);
    }

    /**
     * @expectedException \Biz\MoneyCard\MoneyCardException
     */
    public function testUnlockMoneyCardWithBadBatchStatus()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'get', 'returnValue' => $this->getFakeMoneyCard()],
        ]);

        $batch = $this->getFakeMoneyCardBatch();
        $batch['batchStatus'] = 'invalid';
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'get', 'returnValue' => $batch],
        ]);

        $this->getMoneyCardService()->unlockMoneyCard(1);
    }

    /**
     * @expectedException \Biz\MoneyCard\MoneyCardException
     */
    public function testUnlockMoneyCardWithBadCardStatus()
    {
        $card = $this->getFakeMoneyCard();
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'get', 'returnValue' => $card],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'get', 'returnValue' => $this->getFakeMoneyCardBatch()],
        ]);

        $this->getMoneyCardService()->unlockMoneyCard(1);
    }

    public function testUnlockMoneyCardWithEmptyCard()
    {
        $card = $this->getFakeMoneyCard();
        $card['cardStatus'] = 'invalid';
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'get', 'returnValue' => $card],
            ['functionName' => 'update', 'returnValue' => null],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'get', 'returnValue' => $this->getFakeMoneyCardBatch()],
        ]);
        $this->mockBiz('Card:CardService', [
            ['functionName' => 'getCardByCardIdAndCardType', 'returnValue' => []],
        ]);
        $this->getMoneyCardService()->unlockMoneyCard(1);
    }

    public function testUnlockMoneyCardSuccess()
    {
        $card = $this->getFakeMoneyCard();
        $card['cardStatus'] = 'invalid';
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'get', 'returnValue' => $card],
            ['functionName' => 'update', 'withParams' => [1, ['cardStatus' => 'receive']]],
        ]);
        $this->mockBiz('Card:CardService', [
            ['functionName' => 'getCardByCardIdAndCardType', 'returnValue' => ['cardId' => 1, 'userId' => 1]],
            ['functionName' => 'updateCardByCardIdAndCardType', 'withParams' => [$card['id'], 'moneyCard', ['status' => 'receive']]],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'get', 'returnValue' => $this->getFakeMoneyCardBatch()],
        ]);

        $this->getMoneyCardService()->unlockMoneyCard(1);
    }

    public function testDeleteMoneyCard()
    {
        $moneyCard = $this->getFakeMoneyCard();
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'get', 'returnValue' => $this->getFakeMoneyCardBatch()],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'delete'],
            ['functionName' => 'get', 'returnValue' => $moneyCard],
        ]);
        $this->mockBiz('Card:CardService', [
            ['functionName' => 'getCardByCardIdAndCardType', 'returnValue' => ['cardId' => 1, 'userId' => 1]],
            ['functionName' => 'updateCardByCardIdAndCardType', 'withParams' => [$moneyCard['id'], 'moneyCard', ['status' => 'deleted']]],
        ]);

        $this->getMoneyCardService()->deleteMoneyCard($moneyCard['id']);
    }

    /**
     * @expectedException \Biz\MoneyCard\MoneyCardException
     */
    public function testLockBatchWithEmptyMoneyCard()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'get', 'returnValue' => null],
        ]);
        $this->getMoneyCardService()->lockBatch(1);
    }

    public function testLockBatchSuccess()
    {
        $batch = $this->getFakeMoneyCardBatch();
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'get', 'returnValue' => $batch],
            ['functionName' => 'update', 'withParams' => [$batch['id'], ['batchStatus' => 'invalid']]],
        ]);

        $moneyCard = $this->getFakeMoneyCard();
        $moneyCards = [$moneyCard];
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'updateBatchByCardStatus'],
            ['functionName' => 'search', 'withParams' => [
                [
                    'batchId' => $batch['id'],
                    'cardStatus' => 'receive',
                ],
                ['id' => 'ASC'],
                0,
                1000,
            ], 'returnValue' => $moneyCards],
        ]);

        $this->mockBiz('Card:CardService', [
            ['functionName' => 'getCardByCardIdAndCardType', 'returnValue' => ['cardId' => 1, 'userId' => 1]],
            ['functionName' => 'updateCardByCardIdAndCardType', 'withParams' => [$moneyCard['id'], 'moneyCard', ['status' => 'invalid']]],
        ]);

        $this->getMoneyCardService()->lockBatch(1);
    }

    /**
     * @expectedException \Biz\MoneyCard\MoneyCardException
     */
    public function testUnlockBatchWithEmptyBatch()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'get', 'returnValue' => null],
        ]);
        $this->getMoneyCardService()->unlockBatch(1);
    }

    public function testUnlockBatchSuccess()
    {
        $batch = $this->getFakeMoneyCardBatch();
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'get', 'returnValue' => $batch],
            ['functionName' => 'update', 'withParams' => [1, ['batchStatus' => 'normal']]],
        ]);

        $moneyCard = $this->getFakeMoneyCard();
        $moneyCards = [$moneyCard];
        $moneyCardDao = $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'search', 'withParams' => [
                [
                    'batchId' => $batch['id'],
                    'cardStatus' => 'invalid',
                ],
                ['id' => 'ASC'],
                0,
                1000,
            ], 'returnValue' => $moneyCards],
        ]);

        $moneyCardDao->shouldReceive('update')->withAnyArgs();
        $moneyCardDao->shouldReceive('updateBatchByCardStatus')
            ->with(['batchId' => $batch['id'], 'cardStatus' => 'invalid', 'rechargeUserId' => 0], ['cardStatus' => 'normal']);

        $this->mockBiz('Card:CardService', [
            ['functionName' => 'getCardByCardIdAndCardType', 'returnValue' => ['cardId' => 1, 'userId' => 1, 'status' => 'invalid']],
            ['functionName' => 'updateCardByCardIdAndCardType', 'withParams' => [$moneyCard['id'], 'moneyCard', ['status' => 'receive']]],
        ]);

        $this->getMoneyCardService()->unlockBatch(1);
    }

    /**
     * @expectedException \Biz\MoneyCard\MoneyCardException
     */
    public function testDeleteBatchWithEmpty()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'get', 'returnValue' => null],
        ]);
        $this->getMoneyCardService()->deleteBatch(1);
    }

    public function testDeleteBatchSuccess()
    {
        $batch = $this->getFakeMoneyCardBatch();
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'get', 'returnValue' => $batch],
            ['functionName' => 'delete', 'withParams' => [$batch['id']]],
        ]);

        $moneyCard = $this->getFakeMoneyCard();
        $moneyCards = [$moneyCard];
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'search', 'withParams' => [['batchId' => $batch['id']], ['id' => 'ASC'], 0, 1000], 'returnValue' => $moneyCards],
            ['functionName' => 'deleteMoneyCardsByBatchId', 'withParams' => [$batch['id']]],
        ]);

        $this->mockBiz('Card:CardService', [
            ['functionName' => 'getCardByCardIdAndCardType', 'returnValue' => ['cardId' => 1, 'userId' => 1, 'status' => 'invalid']],
            ['functionName' => 'updateCardByCardIdAndCardType', 'withParams' => [$moneyCard['id'], 'moneyCard', ['status' => 'deleted']]],
        ]);

        $this->getMoneyCardService()->deleteBatch(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testMakeRandsWithBadArgs()
    {
        ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'makeRands', [
            1, 1, 1, 1,
        ]);
    }

    public function testMakeRands()
    {
        $moneyCardServiceMock = \Mockery::mock('Biz\MoneyCard\Service\Impl\MoneyCardServiceImpl')->makePartial()->shouldAllowMockingProtectedMethods();
        $moneyCardServiceMock->shouldReceive('blendCrc32')->andReturnValues([1, 2, 3]);
        $moneyCardServiceMock->shouldReceive('makePassword')->andReturn('1234');

        $result = ReflectionUtils::invokeMethod($moneyCardServiceMock, 'makeRands', [8, 3, 'pre', 10]);

        $this->assertEquals([
            1 => '1234',
            2 => '1234',
            3 => '1234',
        ], $result);
    }

    public function testUUidWithNoSplit()
    {
        $result = ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'uuid', [
            10, 'pre', false,
        ]);

        $this->assertEquals(13, strlen($result));
        $this->assertEquals(0, strpos($result, 'pre'));
    }

    public function testUUidWithSplit()
    {
        $result = ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'uuid', [
            10, 'pre', true,
        ]);

        $this->assertEquals(39, strlen($result));
        $this->assertEquals(0, strpos($result, 'pre'));
    }

    public function testBlendCrc32()
    {
        $result = ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'blendCrc32', ['word']);
        $this->assertEquals('word328', $result);
    }

    public function testCheckCrc32()
    {
        $result = ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'checkCrc32', ['word328']);
        $this->assertTrue($result);
    }

    public function testMakePassword()
    {
        $moneyCardServiceMock = \Mockery::mock('Biz\MoneyCard\Service\Impl\MoneyCardServiceImpl')->makePartial()->shouldAllowMockingProtectedMethods();
        $moneyCardServiceMock->shouldReceive('uuid')->andReturn('123');
        $moneyCardServiceMock->shouldReceive('blendCrc32')->andReturn('123');
        $moneyCardServiceMock->shouldReceive('getMoneyCardByPassword')->andReturn(null);
        $result = ReflectionUtils::invokeMethod($moneyCardServiceMock, 'makePassword', ['10']);
        $this->assertEquals('123', $result);
    }

    public function testUpdateBatch()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'update', 'withParams' => [1, ['field1' => 'field']], 'returnValue' => true],
        ]);

        $result = $this->getMoneyCardService()->updateBatch(1, ['field1' => 'field']);
        $this->assertTrue($result);
    }

    public function testUpdateMoneyCard()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'update', 'withParams' => [1, ['field1' => 'field']], 'returnValue' => true],
        ]);

        $result = $this->getMoneyCardService()->updateMoneyCard(1, ['field1' => 'field']);
        $this->assertTrue($result);
    }

    public function testUseMoneyCardWithRecharged()
    {
        $moneyCard = $this->getFakeMoneyCard();
        $moneyCard['cardStatus'] = 'recharged';
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'get', 'returnValue' => $moneyCard],
        ]);
        $result = $this->getMoneyCardService()->useMoneyCard(1, []);
        $this->assertEquals($result, $moneyCard);
    }

    public function testUseMoneyCardWithEmptyCard()
    {
        $moneyCard = $this->getFakeMoneyCard();
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'get', 'returnValue' => $moneyCard],
            ['functionName' => 'update', 'returnValue' => $moneyCard],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'get', 'returnValue' => $this->getFakeMoneyCardBatch()],
            ['functionName' => 'update'],
        ]);
        $this->mockBiz('Pay:AccountService', [
            ['functionName' => 'transferCoin'],
        ]);
        $this->mockBiz('Card:CardService', [
            ['functionName' => 'getCardByCardIdAndCardType', 'returnValue' => null],
            ['functionName' => 'addCard'],
        ]);
        $result = $this->getMoneyCardService()->useMoneyCard(1, ['rechargeUserId' => 1]);
        $this->assertEquals($moneyCard, $result);
    }

    public function testUseMoneyCardWithExistCard()
    {
        $moneyCard = $this->getFakeMoneyCard();
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'get', 'returnValue' => $moneyCard],
            ['functionName' => 'update', 'returnValue' => $moneyCard],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'get', 'returnValue' => $this->getFakeMoneyCardBatch()],
            ['functionName' => 'update'],
        ]);
        $this->mockBiz('Pay:AccountService', [
            ['functionName' => 'transferCoin'],
        ]);
        $this->mockBiz('Card:CardService', [
            ['functionName' => 'getCardByCardIdAndCardType', 'returnValue' => ['id' => 1]],
            ['functionName' => 'updateCardByCardIdAndCardType'],
        ]);
        $result = $this->getMoneyCardService()->useMoneyCard(1, ['rechargeUserId' => 1]);
        $this->assertEquals($moneyCard, $result);
    }

    /**
     * @expectedException \Exception
     */
    public function testUseMoneyCardWithException()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'get', 'throwException' => new \Exception()],
        ]);
        $this->getMoneyCardService()->useMoneyCard(1, []);
    }

    public function testReceiveMoneyCardWithEmptyToken()
    {
        $this->mockBiz('User:TokenService', [
            ['functionName' => 'verifyToken', 'returnValue' => null],
        ]);
        $result = $this->getMoneyCardService()->receiveMoneyCard('', 1);
        $this->assertEquals([
            'code' => 'failed',
            'message' => '学习卡已过期',
        ], $result);
    }

    public function testReceiveMoneyCardWithEmptyBatch()
    {
        $this->mockBiz('User:TokenService', [
            ['functionName' => 'verifyToken', 'returnValue' => ['token' => 1]],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'getBatchByToken', 'returnValue' => null],
        ]);
        $result = $this->getMoneyCardService()->receiveMoneyCard('', 1);
        $this->assertEquals([
            'code' => 'failed',
            'message' => '该链接不存在或已被删除',
        ], $result);
    }

    public function testReceiveMoneyCardWithBadBatchStatus()
    {
        $this->mockBiz('User:TokenService', [
            ['functionName' => 'verifyToken', 'returnValue' => ['token' => 1]],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'getBatchByToken', 'returnValue' => ['batchStatus' => 'invalid']],
        ]);
        $result = $this->getMoneyCardService()->receiveMoneyCard('', 1);
        $this->assertEquals([
            'code' => 'failed',
            'message' => '该学习卡已经作废',
        ], $result);
    }

    public function testReceiveMoneyCardWithHasReceived()
    {
        $this->mockBiz('User:TokenService', [
            ['functionName' => 'verifyToken', 'returnValue' => ['token' => 1]],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'getBatchByToken', 'returnValue' => ['id' => 1, 'batchStatus' => 'ok']],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'search', 'returnValue' => [['id' => '1', 'rechargeTime' => 0]]],
        ]);
        $result = $this->getMoneyCardService()->receiveMoneyCard('', 1);
        $this->assertEquals([
            'batchId' => 1,
            'code' => 'received',
            'message' => '您已经领取该批学习卡',
        ], $result);
    }

    public function testReceiveMoneyCardWithHasUsed()
    {
        $this->mockBiz('User:TokenService', [
            ['functionName' => 'verifyToken', 'returnValue' => ['token' => 1]],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'getBatchByToken', 'returnValue' => ['id' => 1, 'batchStatus' => 'ok']],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'search', 'returnValue' => [['id' => '1', 'rechargeTime' => 10]]],
        ]);
        $result = $this->getMoneyCardService()->receiveMoneyCard('', 1);
        $this->assertEquals([
            'batchId' => 1,
            'code' => 'recharged',
            'message' => '您已经领取并使用该批学习卡',
        ], $result);
    }

    public function testReceiveMoneyCardWithEmptyCards()
    {
        $this->mockBiz('User:TokenService', [
            ['functionName' => 'verifyToken', 'returnValue' => ['token' => 1]],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'getBatchByToken', 'returnValue' => ['id' => 1, 'batchStatus' => 'ok']],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'search', 'returnValue' => []],
        ]);
        $result = $this->getMoneyCardService()->receiveMoneyCard('', 0);
        $this->assertEquals([
            'code' => 'empty',
            'message' => '该批学习卡已经被领完',
        ], $result);
    }

    public function testReceiveMoneyCardSuccess()
    {
        $this->mockBiz('User:TokenService', [
            ['functionName' => 'verifyToken', 'returnValue' => ['token' => 1]],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'getBatchByToken', 'returnValue' => ['id' => 1, 'batchStatus' => 'ok']],
            ['functionName' => 'update', 'returnValue' => $this->getFakeMoneyCardBatch()],
        ]);

        $moneyCard = $this->getFakeMoneyCard();
        $moneyCards = [
            $moneyCard,
        ];
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'search', 'andReturnValues' => [null, $moneyCards]],
            ['functionName' => 'get', 'returnValue' => $moneyCard],
            ['functionName' => 'update', 'returnValue' => $moneyCard],
            ['functionName' => 'count', 'returnValue' => 1],
        ]);
        $this->mockBiz('Card:CardService', [
            ['functionName' => 'addCard'],
            ['functionName' => 'updateCardByCardIdAndCardType'],
        ]);
        $result = $this->getMoneyCardService()->receiveMoneyCard('', 1);
        $this->assertEquals([
            'id' => 1,
            'code' => 'success',
            'message' => '领取成功，请在卡包中查看',
            'batchId' => 1,
        ], $result);
    }

    /**
     * @expectedException \Exception
     */
    public function testReceiveMoneyCardWithException()
    {
        $this->mockBiz('User:TokenService', [
            ['functionName' => 'verifyToken', 'returnValue' => ['token' => 1]],
        ]);
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'getBatchByToken', 'throwException' => new \Exception()],
        ]);
        $this->getMoneyCardService()->receiveMoneyCard('', 1);
    }

    public function testReceiveMoneyCardByPassword()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'update', 'returnValue' => $this->getFakeMoneyCardBatch()],
        ]);

        $fields = [
            'deadline' => date('Y-m-d', time()),
            'cardStatus' => 'normal',
            'rechargeTime' => 0,
        ];
        $moneyCard = $this->getFakeMoneyCard($fields);
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'getMoneyCardByPassword', 'returnValue' => $moneyCard],
            ['functionName' => 'update', 'returnValue' => $moneyCard],
            ['functionName' => 'count', 'returnValue' => 1],
        ]);
        $this->mockBiz('Card:CardService', [
            ['functionName' => 'addCard'],
            ['functionName' => 'updateCardByCardIdAndCardType'],
        ]);
        $result = $this->getMoneyCardService()->receiveMoneyCardByPassword('', 1);
        $this->assertEquals([
            'id' => $moneyCard['id'],
            'code' => 'success',
            'message' => 'money_card.card_receive_success',
        ], $result);
    }

    public function testReceiveMoneyCardByPasswordWithUpdateError()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', [
            ['functionName' => 'update', 'returnValue' => $this->getFakeMoneyCardBatch()],
        ]);

        $fields = [
            'deadline' => date('Y-m-d', time()),
            'cardStatus' => 'normal',
            'rechargeTime' => 0,
        ];
        $moneyCard = $this->getFakeMoneyCard($fields);
        $this->mockBiz('MoneyCard:MoneyCardDao', [
            ['functionName' => 'getMoneyCardByPassword', 'returnValue' => $moneyCard],
            ['functionName' => 'update', 'returnValue' => []],
        ]);
        $result = $this->getMoneyCardService()->receiveMoneyCardByPassword('', 1);
        $this->assertEquals([
            'code' => 'failed',
            'message' => 'money_card.card_receive_fail',
        ], $result);
    }

    public function testCanUseMoneyCard()
    {
        $result = ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'canUseMoneyCard', [[], 1]);
        $this->assertEquals([
            'code' => 'failed',
            'message' => 'money_card.invalid_password',
        ], $result);

        $moneyCard = $this->getFakeMoneyCard(['cardStatus' => 'invalid']);
        $result = ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'canUseMoneyCard', [$moneyCard, 1]);
        $this->assertEquals([
            'code' => 'invalid',
            'message' => 'money_card.invalid_card',
        ], $result);

        $moneyCard = $this->getFakeMoneyCard();
        $result = ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'canUseMoneyCard', [$moneyCard, 2]);
        $this->assertEquals([
            'code' => 'receivedByOther',
            'message' => 'money_card.card_received_by_other',
        ], $result);

        $moneyCard = $this->getFakeMoneyCard();
        $result = ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'canUseMoneyCard', [$moneyCard, 1]);
        $this->assertEquals([
            'id' => $moneyCard['id'],
            'code' => 'received',
            'message' => 'money_card.card_received',
        ], $result);

        $moneyCard = $this->getFakeMoneyCard(['cardStatus' => 'normal']);
        $result = ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'canUseMoneyCard', [$moneyCard, 1]);
        $this->assertEquals([
            'code' => 'recharged',
            'message' => 'money_card.card_used',
        ], $result);

        $moneyCard = $this->getFakeMoneyCard(['cardStatus' => 'normal']);
        $result = ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'canUseMoneyCard', [$moneyCard, 2]);
        $this->assertEquals([
            'code' => 'rechargedByOther',
            'message' => 'money_card.card_used_by_other',
        ], $result);

        $moneyCard = $this->getFakeMoneyCard(['cardStatus' => 'normal', 'rechargeTime' => 0]);
        $result = ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'canUseMoneyCard', [$moneyCard, 2]);
        $this->assertEquals([
            'code' => 'expired',
            'message' => 'money_card.expired_card',
        ], $result);
    }

    private function getFakeMoneyCard($fields = [])
    {
        $default = [
            'id' => 1,
            'money_card' => 123,
            'batchId' => 1,
            'cardId' => 1,
            'cardStatus' => 'receive',
            'rechargeUserId' => 1,
            'deadline' => 1,
            'rechargeTime' => 1,
        ];

        return array_merge($default, $fields);
    }

    private function getFakeMoneyCardBatch()
    {
        return [
            'id' => 1,
            'cardPrefix' => '123',
            'coin' => 100,
            'batchStatus' => 'receive',
            'rechargedNumber' => 1,
        ];
    }

    /**
     * @return \Biz\MoneyCard\Service\MoneyCardService
     */
    private function getMoneyCardService()
    {
        return $this->createService('MoneyCard:MoneyCardService');
    }
}
