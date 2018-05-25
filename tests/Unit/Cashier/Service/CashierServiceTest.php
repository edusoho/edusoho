<?php

namespace Tests\Unit\Cashier\Service;

use Biz\Cashier\Service\CashierService;
use Biz\BaseTestCase;

class CashierServiceTest extends BaseTestCase
{
    public function testCreateTrade()
    {
        $this->mockBiz(
            'Pay:PayService',
            array(
                array(
                    'functionName' => 'createTrade',
                    'returnValue' => array('id' => 111, 'title' => 'title'),
                    'withParams' => array(
                        array('id' => 111, 'title' => 'title'),
                    ),
                ),
            )
        );
        $result = $this->getCashierService()->createTrade(array('id' => 111, 'title' => 'title'));

        $this->assertEquals(array('id' => 111, 'title' => 'title'), $result);
    }

    /**
     * @return CashierService
     */
    protected function getCashierService()
    {
        return $this->createService('Cashier:CashierService');
    }
}
