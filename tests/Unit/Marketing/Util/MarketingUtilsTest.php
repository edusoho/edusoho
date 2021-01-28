<?php

namespace Tests\Unit\Marketing\Utils;

use Biz\BaseTestCase;
use Biz\Marketing\Util\MarketingUtils;

class MarketingUtilsTest extends BaseTestCase
{
    public function testGetSiteInfo()
    {
        $settingService = $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'withParams' => array('site', array()),
                    'returnValue' => array(
                        'logo' => 'files/system/2017/02-08/174306a2fbdf681792.png',
                        'name' => 'hello',
                        'slogan' => 'slogan',
                    ),
                ),
                array(
                    'functionName' => 'get',
                    'withParams' => array('consult', array()),
                    'returnValue' => array(
                        'webchatURI' => 'files/abc.url',
                        'qq' => array(array('number' => 'abc')),
                        'phone' => array(array('number' => 'dds')),
                    ),
                ),
            )
        );

        $webExtension = $this->mockBiz(
            'System:WebExtension',
            array(
                array(
                    'functionName' => 'getFurl',
                    'withParams' => array('system/2017/02-08/174306a2fbdf681792.png'),
                    'returnValue' => 'files/system/2017/02-08/174306a2fbdf681792.png',
                ),
                array(
                    'functionName' => 'getFurl',
                    'withParams' => array('abc.url'),
                    'returnValue' => 'wechat.png',
                ),
            )
        );

        $result = MarketingUtils::getSiteInfo($settingService, $webExtension);

        $this->assertArrayEquals(
            array(
                'name' => 'hello',
                'logo' => 'files/system/2017/02-08/174306a2fbdf681792.png',
                'about' => 'slogan',
                'wechat' => 'wechat.png',
                'qq' => 'abc',
                'telephone' => 'dds',
            ),
            $result
        );

        $settingService->shouldHaveReceived('get')->times(2);
        $webExtension->shouldHaveReceived('getFurl')->times(2);
    }
}
