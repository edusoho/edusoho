<?php

namespace Tests\Unit\MoneyCard;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;

class MoneyCardServiceTest extends BaseTestCase
{
    public function testGetMoneyCard()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
           array('functionName' => 'get', 'withParams' => array(1990, array('lock' => true)), 'returnValue' => $this->getFakeMoneyCard()),
        ));
        $moneyCard = $this->getMoneyCardService()->getMoneyCard(1990, true);
        $this->assertEquals($this->getFakeMoneyCard(), $moneyCard);
    }

    public function testGetMoneyCardByIds()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'getMoneyCardByIds', 'withParams' => array(array(1, 2, 3)), 'returnValue' => $this->getFakeMoneyCard()),
        ));
        $moneyCard = $this->getMoneyCardService()->getMoneyCardByIds(array(1, 2, 3));
        $this->assertEquals($this->getFakeMoneyCard(), $moneyCard);
    }

    public function testGetMoneyCardByPassword()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'getMoneyCardByPassword', 'withParams' => array(1234567), 'returnValue' => $this->getFakeMoneyCard()),
        ));
        $moneyCard = $this->getMoneyCardService()->getMoneyCardByPassword(1234567);
        $this->assertEquals($this->getFakeMoneyCard(), $moneyCard);
    }

    public function testGetBatch()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'withParams' => array(1), 'returnValue' => $this->getFakeMoneyCardBatch()),
        ));
        $batch = $this->getMoneyCardService()->getBatch(1);
        $this->assertEquals($this->getFakeMoneyCardBatch(), $batch);
    }

    public function testSearchMoneyCards()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'search', 'withParams' => array(array(), array(), 0, 1), 'returnValue' => $this->getFakeMoneyCard()),
        ));

        $moneyCard = $this->getMoneyCardService()->searchMoneyCards(array(), array(), 0, 1);
        $this->assertEquals($this->getFakeMoneyCard(), $moneyCard);
    }

    public function testCountMoneyCards()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'count', 'withParams' => array(array()), 'returnValue' => 100),
        ));

        $count = $this->getMoneyCardService()->countMoneyCards(array());
        $this->assertEquals(100, $count);
    }

    public function testSearchBatches()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'search', 'withParams' => array(array(), array(), 0, 1), 'returnValue' => $this->getFakeMoneyCardBatch()),
        ));
        $batch = $this->getMoneyCardService()->searchBatches(array(), array(), 0, 1);
        $this->assertEquals($this->getFakeMoneyCardBatch(), $batch);
    }

    public function testCountBatches()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'count', 'withParams' => array(array()), 'returnValue' => 100),
        ));
        $count = $this->getMoneyCardService()->countBatches(array());
        $this->assertEquals(100, $count);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testCreateMoneyCardWithError1()
    {
        $this->getMoneyCardService()->createMoneyCard(array('money' => -100));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testCreateMoneyCardWithError2()
    {
        $this->getMoneyCardService()->createMoneyCard(array('coin' => -100));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testCreateMoneyCardWithError3()
    {
        $this->getMoneyCardService()->createMoneyCard(array('cardLength' => -100));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testCreateMoneyCardWithError4()
    {
        $this->getMoneyCardService()->createMoneyCard(array('number' => -100));
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testCreateMoneyCardWithError5()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'isCardIdAvailable', 'returnValue' => false),
            array('functionName' => 'getMoneyCardByPassword', 'returnValue' => null),
        ));

        $this->getMoneyCardService()->createMoneyCard(array(
            'cardLength' => 8,
            'number' => 123,
            'cardPrefix' => 'pre',
            'passwordLength' => 6,
            'deadline' => time(),
        ));
    }

    public function testCreateMoneyCardSuccess()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'isCardIdAvailable', 'returnValue' => true),
            array('functionName' => 'getMoneyCardByPassword', 'returnValue' => null),
            array('functionName' => 'create', 'returnValue' => $this->getFakeMoneyCard()),
        ));

        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'create', 'returnValue' => $this->getFakeMoneyCardBatch()),
        ));

        $batch = $this->getMoneyCardService()->createMoneyCard(array(
            'cardLength' => 8,
            'number' => 123,
            'cardPrefix' => 'pre',
            'passwordLength' => 6,
            'deadline' => time(),
        ));

        $this->assertEquals($this->getFakeMoneyCardBatch(), $batch);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testLockMoneyCardWithEmptyCard()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'get', 'returnValue' => null),
        ));

        $this->getMoneyCardService()->lockMoneyCard(1);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testLockMoneyCardWithBadStatus()
    {
        $fakeCard = $this->getFakeMoneyCard();
        $fakeCard['cardStatus'] = 'bad';
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'get', 'returnValue' => $fakeCard),
        ));

        $this->getMoneyCardService()->lockMoneyCard(1);
    }

    public function testLockMoneyCardSuccess()
    {
        $fakeCard = $this->getFakeMoneyCard();
        $fakeCard['cardStatus'] = 'receive';

        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'get', 'returnValue' => $fakeCard),
            array('functionName' => 'update', 'withParams' => array($fakeCard['id'], array('cardStatus' => 'invalid')), 'returnValue' => $fakeCard),
        ));

        $this->mockBiz('Card:CardService', array(
            array('functionName' => 'getCardByCardIdAndCardType', 'withParams' => array($fakeCard['id'], 'moneyCard'), 'returnValue' => array('userId' => 1)),
            array('functionName' => 'updateCardByCardIdAndCardType', 'withParams' => array($fakeCard['id'], 'moneyCard', array('status' => 'invalid'))),
        ));

        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'withParams' => array($fakeCard['id']), 'returnValue' => $this->getFakeMoneyCardBatch()),
        ));

        $this->getMoneyCardService()->lockMoneyCard($fakeCard['id']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testUnlockMoneyCardWithEmpty()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'get', 'returnValue' => null),
        ));

        $this->getMoneyCardService()->unlockMoneyCard(1);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testUnlockMoneyCardWithBadBatchStatus()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'get', 'returnValue' => $this->getFakeMoneyCard()),
        ));

        $batch = $this->getFakeMoneyCardBatch();
        $batch['batchStatus'] = 'invalid';
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'returnValue' => $batch),
        ));

        $this->getMoneyCardService()->unlockMoneyCard(1);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testUnlockMoneyCardWithBadCardStatus()
    {
        $card = $this->getFakeMoneyCard();
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'get', 'returnValue' => $card),
        ));
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'returnValue' => $this->getFakeMoneyCardBatch()),
        ));

        $this->getMoneyCardService()->unlockMoneyCard(1);
    }

    public function testUnlockMoneyCardWithEmptyCard()
    {
        $card = $this->getFakeMoneyCard();
        $card['cardStatus'] = 'invalid';
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'get', 'returnValue' => $card),
            array('functionName' => 'update', 'returnValue' => null),
        ));
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'returnValue' => $this->getFakeMoneyCardBatch()),
        ));
        $this->mockBiz('Card:CardService', array(
            array('functionName' => 'getCardByCardIdAndCardType', 'returnValue' => array()),
        ));
        $this->getMoneyCardService()->unlockMoneyCard(1);
    }

    public function testUnlockMoneyCardSuccess()
    {
        $card = $this->getFakeMoneyCard();
        $card['cardStatus'] = 'invalid';
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'get', 'returnValue' => $card),
            array('functionName' => 'update', 'withParams' => array(1, array('cardStatus' => 'receive'))),
        ));
        $this->mockBiz('Card:CardService', array(
            array('functionName' => 'getCardByCardIdAndCardType', 'returnValue' => array('cardId' => 1, 'userId' => 1)),
            array('functionName' => 'updateCardByCardIdAndCardType', 'withParams' => array($card['id'], 'moneyCard', array('status' => 'receive'))),
        ));
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'returnValue' => $this->getFakeMoneyCardBatch()),
        ));

        $this->getMoneyCardService()->unlockMoneyCard(1);
    }

    public function testDeleteMoneyCard()
    {
        $moneyCard = $this->getFakeMoneyCard();
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'returnValue' => $this->getFakeMoneyCardBatch()),
        ));
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'delete'),
            array('functionName' => 'get', 'returnValue' => $moneyCard),
        ));
        $this->mockBiz('Card:CardService', array(
            array('functionName' => 'getCardByCardIdAndCardType', 'returnValue' => array('cardId' => 1, 'userId' => 1)),
            array('functionName' => 'updateCardByCardIdAndCardType', 'withParams' => array($moneyCard['id'], 'moneyCard', array('status' => 'deleted'))),
        ));

        $this->getMoneyCardService()->deleteMoneyCard($moneyCard['id']);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testLockBatchWithEmptyMoneyCard()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'returnValue' => null),
        ));
        $this->getMoneyCardService()->lockBatch(1);
    }

    public function testLockBatchSuccess()
    {
        $batch = $this->getFakeMoneyCardBatch();
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'returnValue' => $batch),
            array('functionName' => 'update', 'withParams' => array($batch['id'], array('batchStatus' => 'invalid'))),
        ));

        $moneyCard = $this->getFakeMoneyCard();
        $moneyCards = array($moneyCard);
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'updateBatchByCardStatus'),
            array('functionName' => 'search', 'withParams' => array(
                array(
                    'batchId' => $batch['id'],
                    'cardStatus' => 'receive',
                ),
                array('id' => 'ASC'),
                0,
                1000,
            ), 'returnValue' => $moneyCards),
        ));

        $this->mockBiz('Card:CardService', array(
            array('functionName' => 'getCardByCardIdAndCardType', 'returnValue' => array('cardId' => 1, 'userId' => 1)),
            array('functionName' => 'updateCardByCardIdAndCardType', 'withParams' => array($moneyCard['id'], 'moneyCard', array('status' => 'invalid'))),
        ));

        $this->getMoneyCardService()->lockBatch(1);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testUnlockBatchWithEmptyBatch()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'returnValue' => null),
        ));
        $this->getMoneyCardService()->unlockBatch(1);
    }

    public function testUnlockBatchSuccess()
    {
        $batch = $this->getFakeMoneyCardBatch();
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'returnValue' => $batch),
            array('functionName' => 'update', 'withParams' => array(1, array('batchStatus' => 'normal'))),
        ));

        $moneyCard = $this->getFakeMoneyCard();
        $moneyCards = array($moneyCard);
        $moneyCardDao = $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'search', 'withParams' => array(
                array(
                    'batchId' => $batch['id'],
                    'cardStatus' => 'invalid',
                ),
                array('id' => 'ASC'),
                0,
                1000,
            ), 'returnValue' => $moneyCards),
        ));

        $moneyCardDao->shouldReceive('update')->withAnyArgs();
        $moneyCardDao->shouldReceive('updateBatchByCardStatus')
            ->with(array('batchId' => $batch['id'], 'cardStatus' => 'invalid', 'rechargeUserId' => 0), array('cardStatus' => 'normal'));

        $this->mockBiz('Card:CardService', array(
            array('functionName' => 'getCardByCardIdAndCardType', 'returnValue' => array('cardId' => 1, 'userId' => 1, 'status' => 'invalid')),
            array('functionName' => 'updateCardByCardIdAndCardType', 'withParams' => array($moneyCard['id'], 'moneyCard', array('status' => 'receive'))),
        ));

        $this->getMoneyCardService()->unlockBatch(1);
    }

    /**
     * @expectedException \Codeages\Biz\Framework\Service\Exception\ServiceException
     */
    public function testDeleteBatchWithEmpty()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'returnValue' => null),
        ));
        $this->getMoneyCardService()->deleteBatch(1);
    }

    public function testDeleteBatchSuccess()
    {
        $batch = $this->getFakeMoneyCardBatch();
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'returnValue' => $batch),
            array('functionName' => 'delete', 'withParams' => array($batch['id'])),
        ));

        $moneyCard = $this->getFakeMoneyCard();
        $moneyCards = array($moneyCard);
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'search', 'withParams' => array(array('batchId' => $batch['id']), array('id' => 'ASC'), 0, 1000), 'returnValue' => $moneyCards),
            array('functionName' => 'deleteMoneyCardsByBatchId', 'withParams' => array($batch['id'])),
        ));

        $this->mockBiz('Card:CardService', array(
            array('functionName' => 'getCardByCardIdAndCardType', 'returnValue' => array('cardId' => 1, 'userId' => 1, 'status' => 'invalid')),
            array('functionName' => 'updateCardByCardIdAndCardType', 'withParams' => array($moneyCard['id'], 'moneyCard', array('status' => 'deleted'))),
        ));

        $this->getMoneyCardService()->deleteBatch(1);
    }

    /**
     * @expectedException \Exception
     */
    public function testMakeRandsWithBadArgs()
    {
        ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'makeRands', array(
            1, 1, 1, 1
        ));
    }

    public function testMakeRands()
    {
        $moneyCardServiceMock = \Mockery::mock('Biz\MoneyCard\Service\Impl\MoneyCardServiceImpl')->makePartial()->shouldAllowMockingProtectedMethods();
        $moneyCardServiceMock->shouldReceive('blendCrc32')->andReturnValues(array(1, 2, 3));
        $moneyCardServiceMock->shouldReceive('makePassword')->andReturn('1234');

        $result = ReflectionUtils::invokeMethod($moneyCardServiceMock, 'makeRands', array(8, 3, 'pre', 10));

        $this->assertEquals(array(
            1 => '1234',
            2 => '1234',
            3 => '1234',
        ), $result);
    }

    public function testUUidWithNoSplit()
    {
        $result = ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'uuid', array(
            10, 'pre', false
        ));

        $this->assertEquals(13, strlen($result));
        $this->assertEquals(0, strpos($result, 'pre'));
    }

    public function testUUidWithSplit()
    {
        $result = ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'uuid', array(
            10, 'pre', true
        ));

        $this->assertEquals(39, strlen($result));
        $this->assertEquals(0, strpos($result, 'pre'));
    }

    public function testBlendCrc32()
    {
        $result = ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'blendCrc32', array('word'));
        $this->assertEquals('word328', $result);
    }

    public function testCheckCrc32()
    {
        $result = ReflectionUtils::invokeMethod($this->getMoneyCardService(), 'checkCrc32', array('word328'));
        $this->assertTrue($result);
    }

    public function testMakePassword()
    {
        $moneyCardServiceMock = \Mockery::mock('Biz\MoneyCard\Service\Impl\MoneyCardServiceImpl')->makePartial()->shouldAllowMockingProtectedMethods();
        $moneyCardServiceMock->shouldReceive('uuid')->andReturn('123');
        $moneyCardServiceMock->shouldReceive('blendCrc32')->andReturn('123');
        $moneyCardServiceMock->shouldReceive('getMoneyCardByPassword')->andReturn(null);
        $result = ReflectionUtils::invokeMethod($moneyCardServiceMock, 'makePassword', array('10'));
        $this->assertEquals('123', $result);
    }

    public function testUpdateBatch()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'update', 'withParams' => array(1, array('field1' => 'field')), 'returnValue' => true),
        ));

        $result = $this->getMoneyCardService()->updateBatch(1, array('field1' => 'field'));
        $this->assertTrue($result);
    }

    public function testUpdateMoneyCard()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'update', 'withParams' => array(1, array('field1' => 'field')), 'returnValue' => true),
        ));

        $result = $this->getMoneyCardService()->updateMoneyCard(1, array('field1' => 'field'));
        $this->assertTrue($result);
    }

    public function testUseMoneyCardWithRecharged()
    {
        $moneyCard = $this->getFakeMoneyCard();
        $moneyCard['cardStatus'] = 'recharged';
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'get', 'returnValue' => $moneyCard)
        ));
        $result = $this->getMoneyCardService()->useMoneyCard(1, array());
        $this->assertEquals($result, $moneyCard);
    }

    public function testUseMoneyCardWithEmptyCard()
    {
        $moneyCard = $this->getFakeMoneyCard();
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'get', 'returnValue' => $moneyCard),
            array('functionName' => 'update', 'returnValue' => $moneyCard),
        ));
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'returnValue' => $this->getFakeMoneyCardBatch()),
            array('functionName' => 'update'),
        ));
        $this->mockBiz('Pay:AccountService', array(
            array('functionName' => 'transferCoin')
        ));
        $this->mockBiz('Card:CardService', array(
            array('functionName' => 'getCardByCardIdAndCardType', 'returnValue' => null),
            array('functionName' => 'addCard')
        ));
        $result = $this->getMoneyCardService()->useMoneyCard(1, array('rechargeUserId' => 1));
        $this->assertEquals($moneyCard, $result);
    }

    public function testUseMoneyCardWithExistCard()
    {
        $moneyCard = $this->getFakeMoneyCard();
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'get', 'returnValue' => $moneyCard),
            array('functionName' => 'update', 'returnValue' => $moneyCard),
        ));
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'returnValue' => $this->getFakeMoneyCardBatch()),
            array('functionName' => 'update'),
        ));
        $this->mockBiz('Pay:AccountService', array(
            array('functionName' => 'transferCoin')
        ));
        $this->mockBiz('Card:CardService', array(
            array('functionName' => 'getCardByCardIdAndCardType', 'returnValue' => array('id' => 1)),
            array('functionName' => 'updateCardByCardIdAndCardType')
        ));
        $result = $this->getMoneyCardService()->useMoneyCard(1, array('rechargeUserId' => 1));
        $this->assertEquals($moneyCard, $result);
    }

    /**
     * @expectedException \Exception
     */
    public function testUseMoneyCardWithException()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'get', 'throwException' => new \Exception),
        ));
        $this->getMoneyCardService()->useMoneyCard(1, array());
    }

    public function testReceiveMoneyCardWithEmptyToken()
    {
        $this->mockBiz('User:TokenService', array(
            array('functionName' => 'verifyToken', 'returnValue' => null)
        ));
        $result = $this->getMoneyCardService()->receiveMoneyCard('', 1);
        $this->assertEquals(array(
            'code' => 'failed',
            'message' => '无效的链接',
        ), $result);
    }

    public function testReceiveMoneyCardWithEmptyBatch()
    {
        $this->mockBiz('User:TokenService', array(
            array('functionName' => 'verifyToken', 'returnValue' => array('token' => 1))
        ));
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'getBatchByToken', 'returnValue' => null),
        ));
        $result = $this->getMoneyCardService()->receiveMoneyCard('', 1);
        $this->assertEquals(array(
            'code' => 'failed',
            'message' => '该链接不存在或已被删除',
        ), $result);
    }

    public function testReceiveMoneyCardWithBadBatchStatus()
    {
        $this->mockBiz('User:TokenService', array(
            array('functionName' => 'verifyToken', 'returnValue' => array('token' => 1))
        ));
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'getBatchByToken', 'returnValue' => array('batchStatus' => 'invalid')),
        ));
        $result = $this->getMoneyCardService()->receiveMoneyCard('', 1);
        $this->assertEquals(array(
            'code' => 'failed',
            'message' => '该学习卡已经作废',
        ), $result);
    }

    public function testReceiveMoneyCardWithHasReceived()
    {
        $this->mockBiz('User:TokenService', array(
            array('functionName' => 'verifyToken', 'returnValue' => array('token' => 1))
        ));
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'getBatchByToken', 'returnValue' => array('id' => 1, 'batchStatus' => 'ok')),
        ));
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'search', 'returnValue' => array('id' => '1')),
        ));
        $result = $this->getMoneyCardService()->receiveMoneyCard('', 1);
        $this->assertEquals(array(
            'code' => 'failed',
            'message' => '您已经领取该批学习卡',
        ), $result);
    }

    public function testReceiveMoneyCardWithEmptyCards()
    {
        $this->mockBiz('User:TokenService', array(
            array('functionName' => 'verifyToken', 'returnValue' => array('token' => 1))
        ));
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'getBatchByToken', 'returnValue' => array('id' => 1, 'batchStatus' => 'ok')),
        ));
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'search', 'returnValue' => array()),
        ));
        $result = $this->getMoneyCardService()->receiveMoneyCard('', 0);
        $this->assertEquals(array(
            'code' => 'failed',
            'message' => '该批学习卡已经被领完',
        ), $result);
    }

    public function testReceiveMoneyCardSuccess()
    {
        $this->mockBiz('User:TokenService', array(
            array('functionName' => 'verifyToken', 'returnValue' => array('token' => 1))
        ));
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'getBatchByToken', 'returnValue' => array('id' => 1, 'batchStatus' => 'ok')),
            array('functionName' => 'update', 'returnValue' => $this->getFakeMoneyCardBatch()),
        ));

        $moneyCard = $this->getFakeMoneyCard();
        $moneyCards = array(
            $moneyCard
        );
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'search', 'andReturnValues' => array(null, $moneyCards)),
            array('functionName' => 'get', 'returnValue' => $moneyCard),
            array('functionName' => 'update', 'returnValue' => $moneyCard),
            array('functionName' => 'count', 'returnValue' => 1),
        ));
        $this->mockBiz('Card:CardService', array(
            array('functionName' => 'addCard'),
            array('functionName' => 'updateCardByCardIdAndCardType')
        ));
        $result = $this->getMoneyCardService()->receiveMoneyCard('', 1);
        $this->assertEquals(array(
            'id' => 1,
            'code' => 'success',
            'message' => '领取成功，请在卡包中查看',
        ), $result);
    }

    /**
     * @expectedException \Exception
     */
    public function testReceiveMoneyCardWithException()
    {
        $this->mockBiz('User:TokenService', array(
            array('functionName' => 'verifyToken', 'returnValue' => array('token' => 1))
        ));
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'getBatchByToken', 'throwException' => new \Exception),
        ));
        $this->getMoneyCardService()->receiveMoneyCard('', 1);
    }

    private function getFakeMoneyCard()
    {
        return array(
            'id' => 1,
            'money_card' => 123,
            'batchId' => 1,
            'cardId' => 1,
            'cardStatus' => 'receive',
            'rechargeUserId' => 1,
            'deadline' => 1,
            'rechargeTime' => 1,
        );
    }

    private function getFakeMoneyCardBatch()
    {
        return array(
            'id' => 1,
            'cardPrefix' => '123',
            'coin' => 100,
            'batchStatus' => 'receive',
            'rechargedNumber' => 1,
        );
    }

    /**
     * @return \Biz\MoneyCard\Service\MoneyCardService
     */
    private function getMoneyCardService()
    {
        return $this->createService('MoneyCard:MoneyCardService');
    }
}
