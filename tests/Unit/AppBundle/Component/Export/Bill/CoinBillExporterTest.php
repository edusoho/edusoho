<?php

namespace Tests\Unit\Component\Export\Bill;

use Biz\BaseTestCase;
use AppBundle\Component\Export\Bill\CoinBillExporter;

class CoinBillExporterTest extends BaseTestCase
{
    public function testGetTitles()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $expoter = new CoinBillExporter(self::$appKernel->getContainer(), array());
        $result = $expoter->getTitles();
        $this->assertEquals('cashflow.sn', $result[0]);
    }

    public function testBuildCondition()
    {
        self::$appKernel->getContainer()->set('biz', $this->getBiz());
        $expoter = new CoinBillExporter(self::$appKernel->getContainer(), array());
        $result = $expoter->buildCondition(array());
        $this->assertEquals(array('user_id' => 0, 'amount_type' => 'coin'), $result);
    }
}