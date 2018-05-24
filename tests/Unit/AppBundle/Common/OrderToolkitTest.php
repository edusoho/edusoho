<?php

namespace AppBundle\Common\Tests;

use Biz\BaseTestCase;
use AppBundle\Common\OrderToolkit;

class OrderToolkitTest extends BaseTestCase
{
    public function testReomveUnneededLogs()
    {
        $orderLogs = array(
            array(
                'status' => 'order.finished',
            ),
            array(
                'status' => 'order.success',
            ),
            array(
                'status' => 'order.paid',
            ),
        );

        $result = OrderToolkit::reomveUnneededLogs($orderLogs);

        $this->assertEquals(2, count($result));
        $this->assertEquals('order.finished', $result[0]['status']);
        $this->assertEquals('order.paid', $result[1]['status']);
    }
}
