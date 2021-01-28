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
            NameFormItem::FIELD => NameFormItem::class,
            GenderFormItem::FIELD => GenderFormItem::class,
            AgeFormItem::FIELD => AgeFormItem::class,
            BirthdayFormItem::FIELD => BirthdayFormItem::class,
            IdcardFormItem::FIELD => IdcardFormItem::class,
            PhoneFormItem::FIELD => PhoneFormItem::class,
            EmailFormItem::FIELD => EmailFormItem::class,
            WechatFormItem::FIELD => WechatFormItem::class,
            QQFormItem::FIELD => QQFormItem::class,
            WeiboFormItem::FIELD => WeiboFormItem::class,
            ProvinceCityAreaFormItem::FIELD => ProvinceCityAreaFormItem::class,
            AddressDetailFormItem::FIELD => AddressDetailFormItem::class,
            OccupationFormItem::FIELD => OccupationFormItem::class,
            CompanyFormItem::FIELD => CompanyFormItem::class,
            PositionFormItem::FIELD => PositionFormItem::class,
            SchoolFormItem::FIELD => SchoolFormItem::class,
            GradeFormItem::FIELD => GradeFormItem::class,
            ClassFormItem::FIELD => ClassFormItem::class,
            CountryFormItem::FIELD => CountryFormItem::class,
            LanguageFormItem::FIELD => LanguageFormItem::class,
            InterestFormItem::FIELD => InterestFormItem::class,
        ];
    }
}
