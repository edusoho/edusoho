<?php

namespace Tests\Unit\Component\Echarts;

use Biz\BaseTestCase;
use AppBundle\Component\Echats\EchartsBuilder;

class EchartsBuilderTest extends BaseTestCase
{
    public function testCreateLineDefaultData()
    {
        $echarts = new EchartsBuilder();
        $result = $echarts->createLineDefaultData(1, 'Y/m/d', array(
            array(array(
                'id' => 1,
                'date' => date('Y/m/d', time()),
                'count' => 14,
            ),
        ), ));

        $this->assertTrue(!empty($result['xAxis']));
        $this->assertArrayEquals(array(0, 0, 14), $result['series'][0]);
    }

    public function testCreateBarDefaultData()
    {
        $echarts = new EchartsBuilder();
        $result = $echarts->createBarDefaultData(1, 'Y/m/d', array(
            array(array(
                'id' => 1,
                'date' => date('Y/m/d', time()),
                'count' => 14,
            ),
        ), ));

        $this->assertArrayEquals(array(0, 0, 14), $result['series'][0]);
    }

    public function testGenerateDateRange()
    {
        $echarts = new EchartsBuilder();
        $result = $echarts->generateDateRange(7);

        $this->assertEquals(8, count($result));
        $this->assertEquals($result[7], date('Y/m/d', time()));
    }

    public function testGenerateZeroData()
    {
        $echarts = new EchartsBuilder();
        $result = $echarts->generateZeroData(array('2018/01/22', '2018/01/21'));

        $this->assertArrayEquals(array(
            '2018-01-22' => array(
                'count' => 0,
                'date' => '2018-01-22',
            ),
            '2018-01-21' => array(
                'count' => 0,
                'date' => '2018-01-21',
            ),
        ), $result);
    }

    public function testArrayValueRecursive()
    {
        $echarts = new EchartsBuilder();
        $result = $echarts->arrayValueRecursive(array(
            'test' => array(
                'id' => 1,
            ),
            'test2' => array(
                'id' => 2,
            ),
        ), 'id');

        $this->assertArrayEquals(array(1, 2), $result);

        $result = $echarts->arrayValueRecursive(array(
            'test' => array(
                'id' => 1,
            ),
            'test2' => array(
            ),
        ), 'id');

        $this->assertEquals(1, $result);
    }
}
