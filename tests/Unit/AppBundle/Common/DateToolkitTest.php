<?php

namespace AppBundle\Common\Tests;

use AppBundle\Common\DateToolkit;
use Biz\BaseTestCase;

class DateTookitTest extends BaseTestCase
{
    public function testGenerateDateRange()
    {
        $range = DateToolkit::generateDateRange('2016-12-29', '2017-01-05');
        $range2 = DateToolkit::generateDateRange(date('Y-m-d', '1482969600'), date('Y-m-d', '1483574400'));

        $expectedArray = array(
            '2016-12-29',
            '2016-12-30',
            '2016-12-31',
            '2017-01-01',
            '2017-01-02',
            '2017-01-03',
            '2017-01-04',
            '2017-01-05',
        );

        $this->assertArrayEquals($range, $expectedArray);
        $this->assertArrayEquals($range2, $expectedArray);
    }
}
