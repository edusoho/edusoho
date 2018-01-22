<?php

namespace Tests\Unit\MoneyCard\Dao;

use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;

class MoneyCardDaoImplTest extends BaseTestCase
{
    public function testDeleteMoneyCardsByBatchId()
    {
        $card = $this->getMoneyCardDao()->create(array('cardId' => 4, 'password' => 'aas', 'deadline' => 1,  'cardStatus' => 'invalid', 'batchId' => 1));
        $card1 = $this->getMoneyCardDao()->create(array('cardId' => 4, 'password' => 'aas', 'deadline' => 1,  'cardStatus' => 'normal', 'batchId' => 2));       
        $this->getMoneyCardDao()->deleteMoneyCardsByBatchId(2);

        $this->assertTrue(empty($this->getMoneyCardDao()->get($card1['id'])));
        $this->assertTrue(!empty($this->getMoneyCardDao()->get($card['id'])));
    }

    public function testUpdateBatchByCardStatus()
    { 
        $card = $this->getMoneyCardDao()->create(array('cardId' => 4, 'password' => 'aas', 'deadline' => 1,  'cardStatus' => 'invalid'));
        $card1 = $this->getMoneyCardDao()->create(array('cardId' => 4, 'password' => 'aas', 'deadline' => 1,  'cardStatus' => 'normal'));
        $this->getMoneyCardDao()->updateBatchByCardStatus(
            array(
                'cardStatus' => 'invalid',
            ),
            array('cardStatus' => 'normal')
        );

        $result = $this->getMoneyCardDao()->get($card['id']);
        
        $this->assertEquals('normal', $result['cardStatus']);
    }

    public function testIsCardIdAvailable()
    {
        $card = $this->getMoneyCardDao()->create(array('cardId' => 4, 'password' => 'aas', 'deadline' => 1));
        $result = $this->getMoneyCardDao()->isCardIdAvailable(array($card['cardId']));
        $this->assertNotTrue($result);

        $result = $this->getMoneyCardDao()->isCardIdAvailable(array(41));
        $this->assertTrue($result);
    }

    public function testGetMoneyCardByPassword()
    {
        $card = $this->getMoneyCardDao()->create(array('cardId' => 1, 'password' => 'aas', 'deadline' => 1));
        $result = $this->getMoneyCardDao()->getMoneyCardByPassword('aas');
        $this->assertTrue(!empty($result));
    }

    public function testGetMoneyCardByIds()
    {
        $card = $this->getMoneyCardDao()->create(array('cardId' => 1, 'password' => 1, 'deadline' => 1));
        $card1 = $this->getMoneyCardDao()->create(array('cardId' => 1, 'password' => 1, 'deadline' => 1));
        $result = $this->getMoneyCardDao()->getMoneyCardByIds(array($card['id']));

        $this->assertEquals(1, count($result));
        $this->assertEquals($card['id'], $result[0]['id']);
    }

    public function testDeclares()
    {
        $declares =  array(
            'timestamps' => array(),
            'serializes' => array(),
            'orderbys' => array('id', 'createdTime'),
            'conditions' => array(
                'id = :id',
                'rechargeUserId = :rechargeUserId',
                'cardId = :cardId',
                'cardId in ( :cardIds)',
                'cardStatus = :cardStatus',
                'deadline = :deadline',
                'batchId = :batchId',
                'deadline <= :deadlineSearchEnd',
                'deadline >= :deadlineSearchBegin',
                'receiveTime > :receiveTime_GT',
            ),
        );

        $result = $this->getMoneyCardDao()->declares();
        
        $this->assertArrayEquals($declares, $result);
    }

    private function getMoneyCardDao()
    {
        return $this->createDao('MoneyCard:MoneyCardDao');
    }
}