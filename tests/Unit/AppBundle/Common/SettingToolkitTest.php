<?php

namespace Tests\Unit\AppBundle\Common;

use AppBundle\Common\SettingToolkit;
use Biz\BaseTestCase;

class SettingToolkitTest extends BaseTestCase
{
    public function testGetSetting()
    {
        $this->mockBiz('System:SettingService', array(
            array(
                'functionName' => 'get',
                'withParams' => array('emptyValue', 'default'),
                'returnValue' => null,
            ),
            array(
                'functionName' => 'get',
                'withParams' => array('emptyNames', 'default'),
                'returnValue' => array('a'),
            ),
            array(
                'functionName' => 'get',
                'withParams' => array('namesWithoutKey', 'default'),
                'returnValue' => array('a'),
            ),
            array(
                'functionName' => 'get',
                'withParams' => array('namesWithKey', 'default'),
                'returnValue' => array(
                    'expected' => 'a',
                ),
            ),
        ));

        $this->assertEquals('default', SettingToolkit::getSetting('', 'default'));
        $this->assertEquals('default', SettingToolkit::getSetting('emptyValue', 'default'));
        $this->assertEquals(array('a'), SettingToolkit::getSetting('emptyNames', 'default'));
        $this->assertEquals('default', SettingToolkit::getSetting('namesWithoutKey.expected', 'default'));
        $this->assertEquals('a', SettingToolkit::getSetting('namesWithKey.expected', 'default'));
    }
}
