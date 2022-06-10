<?php

namespace Biz\Live\Dao\Impl;

use Biz\Live\Dao\LiveProviderTeacherDao;
use Codeages\Biz\Framework\Dao\GeneralDaoImpl;

class LiveProviderTeacherDaoImpl extends GeneralDaoImpl implements LiveProviderTeacherDao
{
    protected $table = 'live_provider_teacher';

    public function getByUserIdAndProvider($userId, $provider)
    {
        return $this->getByFields(['userId' => $userId, 'provider' => $provider]);
    }

    public function declares()
    {
        return [
            'timestamps' => ['createdTime'],
        ];
    }
}
