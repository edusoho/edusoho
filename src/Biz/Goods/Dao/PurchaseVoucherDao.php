<?php

namespace Biz\Goods\Dao;

use Codeages\Biz\Framework\Dao\AdvancedDaoInterface;

/**
 * Interface PurchaseVoucherDao
 */
interface PurchaseVoucherDao extends AdvancedDaoInterface
{
    public function getBySpecsId($specsId);

    public function findByIds($ids);
}
