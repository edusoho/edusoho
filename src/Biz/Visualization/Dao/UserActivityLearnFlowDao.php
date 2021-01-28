<?php

namespace Biz\Visualization\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface UserActivityLearnFlowDao extends AdvancedDaoInterface
{
    public function getByUserIdAndSign($userId, $sign);

    public function setUserOtherFlowUnActive($userId, $activeSign);

    public function getUserLatestActiveFlow($userId);
}
