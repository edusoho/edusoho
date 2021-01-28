<?php

namespace Tests\Unit\Component\DeviceDetector;

use Biz\BaseTestCase;
use AppBundle\Component\DeviceDetector\DeviceDetectorAdapter;

class DeviceDetectorAdapterTest extends BaseTestCase
{
    public function testAndroidPc()
    {
        $detector = new DeviceDetectorAdapter('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_2) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/65.0.3325.181 Safari/537.36');
        $this->assertFalse($detector->isMobile());
        $this->assertEquals('desktop', $detector->getDevice());
        $this->assertEquals(
            array(
                'name' => 'Mac',
                'short_name' => 'MAC',
                'version' => '10.13',
                'platform' => '',
            ),
            $detector->getOs()
        );
        $this->assertFalse($detector->isBot());

        $this->assertEquals(
            array(
                'type' => 'browser',
                'name' => 'Chrome',
                'short_name' => 'CH',
                'version' => '65.0',
                'engine' => 'Blink',
                'engine_version' => '',
            ),
            $detector->getClient()
        );
    }
}
