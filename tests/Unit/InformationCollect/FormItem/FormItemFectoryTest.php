<?php

namespace Tests\Unit\InformationCollect\FormItem;

use Biz\BaseTestCase;
use Biz\InformationCollect\FormItem\FormItemFectory;

class FormItemFectoryTest extends BaseTestCase
{
    public function testCreate()
    {
        $this->assertEquals(true, FormItemFectory::create('name') instanceof \Biz\InformationCollect\FormItem\NameFormItem);
        $this->assertEquals(true, FormItemFectory::create('gender') instanceof \Biz\InformationCollect\FormItem\GenderFormItem);
        $this->assertEquals(true, FormItemFectory::create('age') instanceof \Biz\InformationCollect\FormItem\AgeFormItem);
        $this->assertEquals(true, FormItemFectory::create('birthday') instanceof \Biz\InformationCollect\FormItem\BirthdayFormItem);
        $this->assertEquals(true, FormItemFectory::create('idcard') instanceof \Biz\InformationCollect\FormItem\IdcardFormItem);
        $this->assertEquals(true, FormItemFectory::create('phone') instanceof \Biz\InformationCollect\FormItem\PhoneFormItem);
        $this->assertEquals(true, FormItemFectory::create('email') instanceof \Biz\InformationCollect\FormItem\EmailFormItem);
        $this->assertEquals(true, FormItemFectory::create('wechat') instanceof \Biz\InformationCollect\FormItem\WechatFormItem);
        $this->assertEquals(true, FormItemFectory::create('qq') instanceof \Biz\InformationCollect\FormItem\QQFormItem);
        $this->assertEquals(true, FormItemFectory::create('weibo') instanceof \Biz\InformationCollect\FormItem\WeiboFormItem);
        $this->assertEquals(true, FormItemFectory::create('province_city_area') instanceof \Biz\InformationCollect\FormItem\ProvinceCityAreaFormItem);
        $this->assertEquals(true, FormItemFectory::create('address_detail') instanceof \Biz\InformationCollect\FormItem\AddressDetailFormItem);
        $this->assertEquals(true, FormItemFectory::create('occupation') instanceof \Biz\InformationCollect\FormItem\OccupationFormItem);
        $this->assertEquals(true, FormItemFectory::create('company') instanceof \Biz\InformationCollect\FormItem\CompanyFormItem);
        $this->assertEquals(true, FormItemFectory::create('position') instanceof \Biz\InformationCollect\FormItem\PositionFormItem);
        $this->assertEquals(true, FormItemFectory::create('school') instanceof \Biz\InformationCollect\FormItem\SchoolFormItem);
        $this->assertEquals(true, FormItemFectory::create('grade') instanceof \Biz\InformationCollect\FormItem\GradeFormItem);
        $this->assertEquals(true, FormItemFectory::create('class') instanceof \Biz\InformationCollect\FormItem\ClassFormItem);
        $this->assertEquals(true, FormItemFectory::create('country') instanceof \Biz\InformationCollect\FormItem\CountryFormItem);
        $this->assertEquals(true, FormItemFectory::create('language') instanceof \Biz\InformationCollect\FormItem\LanguageFormItem);
        $this->assertEquals(true, FormItemFectory::create('interest') instanceof \Biz\InformationCollect\FormItem\InterestFormItem);
    }

    public function testGetFormItems()
    {
        $formItems = FormItemFectory::getFormItems();

        $this->assertEquals($formItems, [
            'name' => 'Biz\InformationCollect\FormItem\NameFormItem',
            'gender' => 'Biz\InformationCollect\FormItem\GenderFormItem',
            'age' => 'Biz\InformationCollect\FormItem\AgeFormItem',
            'birthday' => 'Biz\InformationCollect\FormItem\BirthdayFormItem',
            'idcard' => 'Biz\InformationCollect\FormItem\IdcardFormItem',
            'phone' => 'Biz\InformationCollect\FormItem\PhoneFormItem',
            'email' => 'Biz\InformationCollect\FormItem\EmailFormItem',
            'wechat' => 'Biz\InformationCollect\FormItem\WechatFormItem',
            'qq' => 'Biz\InformationCollect\FormItem\QQFormItem',
            'weibo' => 'Biz\InformationCollect\FormItem\WeiboFormItem',
            'province_city_area' => 'Biz\InformationCollect\FormItem\ProvinceCityAreaFormItem',
            'address_detail' => 'Biz\InformationCollect\FormItem\AddressDetailFormItem',
            'occupation' => 'Biz\InformationCollect\FormItem\OccupationFormItem',
            'company' => 'Biz\InformationCollect\FormItem\CompanyFormItem',
            'position' => 'Biz\InformationCollect\FormItem\PositionFormItem',
            'school' => 'Biz\InformationCollect\FormItem\SchoolFormItem',
            'grade' => 'Biz\InformationCollect\FormItem\GradeFormItem',
            'class' => 'Biz\InformationCollect\FormItem\ClassFormItem',
            'country' => 'Biz\InformationCollect\FormItem\CountryFormItem',
            'language' => 'Biz\InformationCollect\FormItem\LanguageFormItem',
            'interest' => 'Biz\InformationCollect\FormItem\InterestFormItem',
        ]);
    }
}
