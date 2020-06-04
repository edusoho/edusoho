<?php

namespace Biz\Goods\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface GoodsSpecsDao extends GeneralDaoInterface
{
    public function getByGoodsIdAndTargetId($goodsId, $targetId);

    public function findByGoodsId($goodsId);

    public function deleteByGoodsIdAndTargetId($goodsId, $targetId);

    public function deleteByGoodsId($goodsId);
}
