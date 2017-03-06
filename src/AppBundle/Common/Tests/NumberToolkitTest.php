<?php

namespace AppBundle\Common\Tests;

use Biz\BaseTestCase;
use AppBundle\Common\NumberToolkit;

class NumberToolkitTest extends BaseTestCase
{
    public function testroundUp()
    {
        $testNumArray = array(123.456, 5784975.4328278, 437.30, 89.11, 345.030);

        $testNum1 = NumberToolkit::roundUp($testNumArray[0]);
        $testNum2 = NumberToolkit::roundUp($testNumArray[1]);
        $testNum3 = NumberToolkit::roundUp($testNumArray[2]);
        $testNum4 = NumberToolkit::roundUp($testNumArray[3]);
        $testNum5 = NumberToolkit::roundUp($testNumArray[4]);

        $this->assertEquals(123.46, $testNum1);
        $this->assertEquals(5784975.44, $testNum2);
        $this->assertEquals(437.30, $testNum3);
        $this->assertEquals(89.11, $testNum4);
        $this->assertEquals(345.03, $testNum5);
    }
}
