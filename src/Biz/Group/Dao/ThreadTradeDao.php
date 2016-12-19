<?php
namespace Biz\Group\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface ThreadTradeDao extends GeneralDaoInterface
{
    public function getTrade($id);

    public function addTrade($fields);

    public function getTradeByUserIdAndThreadId($userId, $threadId);

    public function getTradeByUserIdAndGoodsId($userId, $goodsId);
}
