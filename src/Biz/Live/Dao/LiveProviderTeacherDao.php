<?php

namespace Biz\Live\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface LiveProviderTeacherDao extends GeneralDaoInterface
{
    public function getByUserIdAndProvider($userId, $provider);
}
