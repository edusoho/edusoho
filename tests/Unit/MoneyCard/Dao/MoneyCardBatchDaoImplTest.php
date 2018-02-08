<?php

namespace Tests\Unit\MoneyCard\Dao;

use Biz\BaseTestCase;

class MoneyCardBatchDaoImplTest extends BaseTestCase
{
    public function testGetBatchByToken()
    {
        $card = $this->getMoneyCardBatchDao()->create(array(
            'token' => 'asdf',
            'cardPrefix' => 1,
            'deadline' => 1,
            'note' => 123,
        ));

        $result = $this->getMoneyCardBatchDao()->getBatchByToken('asdf');
        $this->assertEquals($card['id'], $result['id']);

        $result = $this->getMoneyCardBatchDao()->getBatchByToken('asdf', array('lock' => true));
        $this->assertEquals($card['id'], $result['id']);
    }

    public function testDeclares()
    {
        $result = $this->createDao('MoneyCard:MoneyCardBatchDao')->declares();
        $declare = array(
            'timestamps' => array(),
            'serializes' => array(),
            'orderbys' => array('id', 'createdTime'),
            'conditions' => array(
                'cardPrefix = :cardPrefix',
                'batchName LIKE :batchName',
            ),
        );

        $this->assertArrayEquals($declare, $result);
    }

    private function getMoneyCardBatchDao()
    {
        return $this->createDao('MoneyCard:MoneyCardBatchDao');
    }
}
