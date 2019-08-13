<?php

namespace Tests\Unit\Component\Activity;

use Biz\BaseTestCase;
use AppBundle\Component\Activity\ActivityConfig;

class ActivityConfigTest extends BaseTestCase
{
    public function testOffsetExists()
    {
        $config = new ActivityConfig(array());
        $result = isset($config['test']);
        $this->assertFalse($result);

        $result = $config->offsetExists('test');
        $this->assertFalse($result);
    }

    public function testOffsetGet()
    {
        $config = new ActivityConfig(array('test' => '11'));
        $this->assertEquals('11', $config['test']);
        $this->assertEquals('11', $config->offsetGet('test'));
    }

    /**
     * @expectedException AppBundle\Common\Exception\UnexpectedValueException
     */
    public function testOffsetGetWithNotExist()
    {
        $config = new ActivityConfig(array());
        $config->offsetGet('test');
    }

    public function testOffsetSet()
    {
        $config = new ActivityConfig(array());
        $config['test'] = '22';
        $this->assertEquals('22', $config['test']);

        $config->offsetSet('test', '33');
        $this->assertEquals('33', $config['test']);
    }

    public function testOffsetUnset()
    {
        $config = new ActivityConfig(array('test' => '2233'));
        unset($config['test']);
        $result = isset($config['test']);
        $this->assertFalse($result);

        $config['test'] = '121';
        $config->offsetUnset('test');
        $result = isset($config['test']);
        $this->assertFalse($result);
    }
}
