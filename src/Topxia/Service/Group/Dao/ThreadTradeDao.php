<?php

namespace Topxia\Service\Group\Dao;

interface ThreadTradeDao
{
    public function getTrade($id);

    public function addTrade($fields);
    
    public function getTradeByUserIdAndThreadId($userId,$threadId);

    public function getTradeByUserIdAndGoodsId($userId,$goodsId);
}