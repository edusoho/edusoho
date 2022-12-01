<?php

namespace MarketingMallBundle\Biz\MallAdminProfile\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface MallAdminProfileDao extends GeneralDaoInterface
{
    public function getByUserIdAndFieldName($userId, $fieldName);
}
