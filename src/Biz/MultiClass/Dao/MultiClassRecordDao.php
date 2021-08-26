<?php

namespace Biz\MultiClass\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface MultiClassRecordDao extends AdvancedDaoInterface
{
    public function getRecordBySign($sign);

    public function findByUserIdAndIsPush($userId, $isPush);
}
