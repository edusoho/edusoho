<?php

namespace Biz\WrongBook\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface WrongQuestionCollectDao extends AdvancedDaoInterface
{
    public function getCollectBYPoolIdAndItemId($poolId, $itemId);
}
