<?php

namespace Tests\Unit\AppBundle\Component\Wrapper;

use Biz\BaseTestCase;

class WrapperManageTest extends BaseTestCase
{
    public function testHandle()
    {
        $currency = self::getContainer()->get('translator')->trans('admin.account_center.RMB');
        $wrapper = self::getContainer()->get('web.wrapper');
        $courseSet = $this->getCourseSetService()->createCourseSet(array('type' => 'normal', 'title' => 'course set1 title'));
        //1.免费商品的价格显示内容
        $courseSet['maxCoursePrice'] = 0;
        $result = $wrapper->handle($courseSet, 'courseSet.price');
        $priceWrapper = $result['priceWrapper'];
        $this->assertEquals(self::getContainer()->get('translator')->trans('course.block_grid.price_free'), $priceWrapper['priceText']);
        $this->assertEquals('RMB', $priceWrapper['currencyType']);
        $this->assertEquals($currency, $priceWrapper['currency']);

        //2.非免费商品
        //2.1.无虚拟币
        //2.1.1最小价格与最大价格一致
        $courseSet['minCoursePrice'] = 1;
        $courseSet['maxCoursePrice'] = 1;
        $result = $wrapper->handle($courseSet, 'courseSet.price');
        $priceWrapper = $result['priceWrapper'];
        $this->assertEquals('1'.$currency, $priceWrapper['priceText']);
        $this->assertEquals('RMB', $priceWrapper['currencyType']);
        $this->assertEquals($currency, $priceWrapper['currency']);

        //2.1.2最小价格与最大价格不一致
        $courseSet['maxCoursePrice'] = 2;
        $result = $wrapper->handle($courseSet, 'courseSet.price');
        $priceWrapper = $result['priceWrapper'];

        $this->assertEquals(self::getContainer()->get('translator')->trans('course.minimum_price.unit', array('%price%' => 1, '%unit%' => $currency)), $priceWrapper['priceText']);
        $this->assertEquals('RMB', $priceWrapper['currencyType']);
        $this->assertEquals($currency, $priceWrapper['currency']);

        //2.2.有虚拟币
        $courseSet['maxCoursePrice'] = 1;
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
        $result = $wrapper->handle($courseSet, 'courseSet.price');
        $priceWrapper = $result['priceWrapper'];
        $this->assertEquals('0.5'.self::getContainer()->get('translator')->trans('finance.coin'), $priceWrapper['priceText']);
        $this->assertEquals('coin', $priceWrapper['currencyType']);
        $this->assertEquals(self::getContainer()->get('translator')->trans('finance.coin'), $priceWrapper['currency']);
    }

    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
