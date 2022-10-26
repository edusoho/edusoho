<?php

namespace MarketingMallBundle\Biz\MallAdminProfile\Dao\Impl;

use Codeages\Biz\Framework\Dao\GeneralDaoImpl;
use MarketingMallBundle\Biz\MallAdminProfile\Dao\MallAdminProfileDao;

class MallAdminProfileDaoImpl extends GeneralDaoImpl implements MallAdminProfileDao
{
    protected $table = 'marketing_mall_admin_profile';

    public function getByUserIdAndFieldName($userId, $fieldName)
    {
        return $this->getByFields(['userId' => $userId, 'field' => $fieldName]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime', 'updatedTime'],
        ];
    }
}
