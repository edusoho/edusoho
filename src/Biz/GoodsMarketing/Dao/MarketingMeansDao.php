<?php

namespace Biz\GoodsMarketing\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

/**
 * Interface MarketingMeansDao
 */
interface MarketingMeansDao extends AdvancedDaoInterface
{
    public function findValidMeansByTargetTypeAndTargetId($targetType, $targetId);
}
