<?php

namespace MarketingMallBundle\Biz\MallAdminProfile\Service\Impl;

use Biz\BaseService;
use MarketingMallBundle\Biz\MallAdminProfile\Dao\MallAdminProfileDao;
use MarketingMallBundle\Biz\MallAdminProfile\Service\MallAdminProfileService;

class MallAdminProfileServiceImpl extends BaseService implements MallAdminProfileService
{
    public function getMallAdminProfileByUserIdAndFieldName($userId, $fieldName)
    {
        return $this->getMallAdminProfileDao()->getByUserIdAndFieldName($userId, $fieldName);
    }

    public function setMallAdminProfile($userId, $fieldName, $value)
    {
        $profile = $this->getMallAdminProfileByUserIdAndFieldName($userId, $fieldName);
        if ($profile) {
            return $this->getMallAdminProfileDao()->update($profile['id'], ['val' => $value]);
        }

        return $this->getMallAdminProfileDao()->create([
            'userId' => $userId,
            'field' => $fieldName,
            'val' => $value,
        ]);
    }

    /**
     * @return MallAdminProfileDao
     */
    protected function getMallAdminProfileDao()
    {
        return $this->createDao('MallAdminProfile:MallAdminProfileDao');
    }
}
