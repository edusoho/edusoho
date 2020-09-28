<?php

namespace Biz\InformationCollect\FormItem;

class FormItemFectory
{
    public static function create($code)
    {
        $formItem = self::getFormItems()[$code];

        return new $formItem();
    }

    public static function getFormItems()
    {
        return [
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
        ];
    }
}
