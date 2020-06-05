<?php

namespace Biz\Goods\Service\Impl;

use Biz\BaseService;
use Biz\Goods\Dao\PurchaseVoucherDao;

class PurchaseServiceImpl extends BaseService
{
    public function getVoucher($id)
    {
        return $this->getPurchaseVoucherDao()->get($id);
    }

    public function createVoucher($voucher)
    {
    }

    public function updateVoucher($id, $voucher)
    {
    }

    public function deleteVoucher($id)
    {
    }

    public function countVouchers($conditions)
    {
    }

    public function searchVouchers()
    {
    }

    public function findVoucherByIds($ids)
    {
    }

    /**
     * @return PurchaseVoucherDao
     */
    protected function getPurchaseVoucherDao()
    {
        return $this->createDao('Goods:PurchaseVoucherDao');
    }
}
