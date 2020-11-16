<?php

namespace Biz\Visualization\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface UserActivityLearnFlowDao extends AdvancedDaoInterface
{
    public function getBySign($sign);
}
