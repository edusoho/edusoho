<?php

namespace Biz\WrongBook\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface WrongQuestionCollectDao extends AdvancedDaoInterface
{
    public function getCollectBYPoolIdAndItemId($poolId, $itemId);

    public function getCollectBYPoolId($poolId);

    public function getCollectIdsBYPoolIds($poolIds);

    public function deleteCollectByPoolIds($poolIds);

    public function findCollectByItemIds($itemIds);
}
