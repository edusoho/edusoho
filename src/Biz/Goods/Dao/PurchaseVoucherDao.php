<?php

namespace Biz\Goods\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

interface PurchaseVoucherDao extends AdvancedDaoInterface
{
    public function getBySpecsId($specsId);
}
