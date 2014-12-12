<?php

namespace Topxia\Service\Group\Dao;

interface ThreadGoodsDao
{
    public function getGoods($id);

    public function addGoods($fields);

    public function updateGoods($id,$fields);    
    
    public function waveGoods($id, $field, $diff);

    public function deleteGoodsByThreadId($id,$type);

    public function deleteGoods($id);

    public function sumGoodsCoins($conditions);

    public function searchGoods($conditions,$orderBy,$start,$limit);

    public function waveGoodsHitNum($goodsId);
}