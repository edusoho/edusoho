<?php

namespace Tests\Unit\AppBundle\Component\Wrapper;

use Biz\BaseTestCase;
use AppBundle\Component\Wrapper\ClassroomWrapper;
use AppBundle\Common\ReflectionUtils;

class ClassroomWrapperTest extends BaseTestCase
{
    public function testPrice()
    {
        $wrapper = new ClassroomWrapper(self::getContainer());
        $currency = self::getContainer()->get('translator')->trans('admin.account_center.RMB');
        $classroom = array('price' => 0);
        $result = $wrapper->price($classroom);
        $this->assertEquals(
            $result['priceWrapper']['priceText'],
            self::getContainer()->get('translator')->trans('course.block_grid.price_free')
        );

        $classroom = array('price' => '1');
        $result = $wrapper->price($classroom);
        $this->assertEquals($result['priceWrapper']['currencyType'], 'RMB');
        $this->assertEquals(
            $result['priceWrapper']['priceText'],
            $classroom['price'].$currency
        );

        $this->mockBiz(
            'System:SettingService',
            array(
                array(
                    'functionName' => 'get',
                    'returnValue' => array(
                        'coin_enabled' => 1,
                        'cash_model' => 'currency',
                        'cash_rate' => 0.5,
                        'coin_name' => '',
                    ),
                ),
            )
        );
        $result = $wrapper->price($classroom);
        $this->assertEquals($result['priceWrapper']['currencyType'], 'coin');
        $this->assertEquals(
            $result['priceWrapper']['priceText'],
            '0.5'.self::getContainer()->get('translator')->trans('finance.coin')
        );
    }

    public function testGetWrapList()
    {
        $wrapper = new ClassroomWrapper(self::getContainer());
        $result = ReflectionUtils::invokeMethod($wrapper, 'getWrapList');

        $this->assertArrayEquals($result, array('price'));
    }
}
