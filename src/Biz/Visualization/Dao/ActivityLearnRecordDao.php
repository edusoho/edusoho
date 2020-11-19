<?php

namespace Biz\Visualization\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface ActivityLearnRecordDao extends AdvancedDaoInterface
{
    public function getUserLastLearnRecord($userId);

    public function getUserLastLearnRecordBySign($userId, $sign);
}
