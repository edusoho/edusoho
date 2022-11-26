<?php

namespace MarketingMallBundle\Biz\MallAdminProfile\Service;

interface MallAdminProfileService
{
    public function getMallAdminProfileByUserIdAndFieldName($userId, $fieldName);

    public function setMallAdminProfile($userId, $fieldName, $value);
}
