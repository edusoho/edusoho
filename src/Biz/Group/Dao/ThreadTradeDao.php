<?php

namespace Biz\Group\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThreadTradeDao extends GeneralDaoInterface
{
    public function getByUserIdAndThreadId($userId, $threadId);

    public function getByUserIdAndGoodsId($userId, $goodsId);
}
