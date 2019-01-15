<?php

namespace Biz\PushDevice\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface PushDeviceDao extends GeneralDaoInterface
{
    public function getByRegId($regId);

    public function findByUserIds($userIds);
}
