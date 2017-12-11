<?php

namespace Tests\Unit\MoneyCard;

use Biz\BaseTestCase;

class MoneyCardServiceTest extends BaseTestCase
{
    public function testGetMoneyCard()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
           array('functionName' => 'get', 'withParams' => array(1990, array('lock' => true)), 'returnValue' => $this->getFakeMoneyCard())
        ));
        $moneyCard = $this->getMoneyCardService()->getMoneyCard(1990, true);
        $this->assertEquals($this->getFakeMoneyCard(), $moneyCard);
    }

    public function testGetMoneyCardByIds()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'getMoneyCardByIds', 'withParams' => array(array(1, 2, 3)), 'returnValue' => $this->getFakeMoneyCard())
        ));
        $moneyCard = $this->getMoneyCardService()->getMoneyCardByIds(array(1, 2, 3));
        $this->assertEquals($this->getFakeMoneyCard(), $moneyCard);
    }

    public function testGetMoneyCardByPassword()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'getMoneyCardByPassword', 'withParams' => array(1234567), 'returnValue' => $this->getFakeMoneyCard())
        ));
        $moneyCard = $this->getMoneyCardService()->getMoneyCardByPassword(1234567);
        $this->assertEquals($this->getFakeMoneyCard(), $moneyCard);
    }

    public function testGetBatch()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'get', 'withParams' => array(1), 'returnValue' => $this->getFakeMoneyCardBatch())
        ));
        $batch = $this->getMoneyCardService()->getBatch(1);
        $this->assertEquals($this->getFakeMoneyCardBatch(), $batch);
    }

    public function testSearchMoneyCards()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'search', 'withParams' => array(array(), array(), 0, 1), 'returnValue' => $this->getFakeMoneyCard())
        ));

        $moneyCard = $this->getMoneyCardService()->searchMoneyCards(array(), array(), 0, 1);
        $this->assertEquals($this->getFakeMoneyCard(), $moneyCard);
    }

    public function testCountMoneyCards()
    {
        $this->mockBiz('MoneyCard:MoneyCardDao', array(
            array('functionName' => 'count', 'withParams' => array(array()), 'returnValue' => 100)
        ));

        $count = $this->getMoneyCardService()->countMoneyCards(array());
        $this->assertEquals(100, $count);
    }

    public function testSearchBatches()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'search', 'withParams' => array(array(), array(), 0, 1), 'returnValue' => $this->getFakeMoneyCardBatch())
        ));
        $batch = $this->getMoneyCardService()->searchBatches(array(), array(), 0, 1);
        $this->assertEquals($this->getFakeMoneyCardBatch(), $batch);
    }

    public function testCountBatches()
    {
        $this->mockBiz('MoneyCard:MoneyCardBatchDao', array(
            array('functionName' => 'count', 'withParams' => array(array()), 'returnValue' => 100)
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
            array('functionName' => 'getMoneyCardByPassword', 'returnValue' => null)
        ));

        $this->getMoneyCardService()->createMoneyCard(array(
            'cardLength' => 8,
            'number' => 123,
            'cardPrefix' => 'pre',
            'passwordLength' => 6,
            'deadline' => time()
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
            'deadline' => time()
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
            array('functionName' => 'getCardByCardIdAndCardType', 'returnValue' => array())
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
                1000
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
                1000
            ), 'returnValue' => $moneyCards),
        ));

        $moneyCardDao->shouldReceive('update')->withAnyArgs();
        $moneyCardDao->shouldReceive('updateBatchByCardStatus')
            ->with(array('batchId' => $batch['id'], 'cardStatus' => 'invalid', 'rechargeUserId' => 0,), array('cardStatus' => 'normal'));

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

    private function getFakeMoneyCard()
    {
        return array(
            'id' => 1,
            'money_card' => 123,
            'batchId' => 1,
            'cardId' => 1,
            'cardStatus' => 'receive',
            'rechargeUserId' => 1
        );
    }

    private function getFakeMoneyCardBatch()
    {
        return array(
            'id' => 1,
            'cardPrefix' => '123',
            'coin' => 100,
            'batchStatus' => 'receive',
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