<?php

namespace Biz\PushMessageMobileDevice\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface PushMessageMobileDeviceDao extends GeneralDaoInterface
{
    public function getByRegId($regId);

    public function findByUserIds($userIds);
}
