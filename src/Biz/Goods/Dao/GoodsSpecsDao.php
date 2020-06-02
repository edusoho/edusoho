<?php

namespace Biz\Goods\Dao;

use Codeages\Biz\Framework\Dao\GeneralDaoInterface;

interface GoodsSpecsDao extends GeneralDaoInterface
{
    public function findByGoodsId($goodsId);
}
