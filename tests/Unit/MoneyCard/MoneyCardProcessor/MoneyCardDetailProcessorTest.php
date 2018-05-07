<?php

namespace Tests\Unit\MoneyCard\MoneyCardProcessor;

use Biz\BaseTestCase;
use AppBundle\Common\ReflectionUtils;
use Biz\MoneyCard\MoneyCardProcessor\MoneyCardDetailProcessor;

class MoneyCardDetailProcessorTest extends BaseTestCase
{
    public function testGetDetailById()
    {
        $this->mockBiz('MoneyCard:MoneyCardService', array(
            array(
                'functionName' => 'getMoneyCard',
                'returnValue' => array('batchId' => 1)
            ),
            array(
                'functionName' => 'getBatch',
                'returnValue' => array('coin' => 1000)
            )
        ));
        $processor = new MoneyCardDetailProcessor();
        $result = $processor->getDetailById(1);

        $this->assertEquals(1, $result['batchId']);
        $this->assertEquals(1000, $result['coin']);
    }

    public function testGetCardDetailsByCardIds()
    {
        $this->mockBiz('MoneyCard:MoneyCardService', array(
            array(
                'functionName' => 'getMoneyCardByIds',
                'returnValue' => array(array('id' => 1, 'batchId' => 1), array('id' => 2, 'batchId' => 2))
            ),
            array(
                'functionName' => 'getBatch',
                'withParams' => array(1),
                'returnValue' => array('coin' => 1000)
            ),
            array(
                'functionName' => 'getBatch',
                'withParams' => array(2),
                'returnValue' => array('coin' => 10)
            )
        ));
        $processor = new MoneyCardDetailProcessor();
        $result = $processor->getCardDetailsByCardIds(array(1, 2));

        $this->assertEquals(2, count($result));
        $this->assertEquals(1010, ($result[0]['coin'] + $result[1]['coin']));
    }
}
