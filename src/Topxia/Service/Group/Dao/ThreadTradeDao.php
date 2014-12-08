<?php

namespace Topxia\Service\Group\Dao;

interface ThreadTradeDao
{
    public function getTrade($id);

    public function addTrade($fields);
    
    public function getTradeByUserIdandThreadId($id,$userId);

    public function getTradeByUserIdandGoodsId($userId,$goodsId);
}