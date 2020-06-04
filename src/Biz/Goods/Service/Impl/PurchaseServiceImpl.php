<?php

namespace Biz\Goods\Service\Impl;

use Biz\BaseService;
use Biz\Goods\Dao\PurchaseVoucherDao;

class PurchaseServiceImpl extends BaseService
{
    public function getVoucher($id)
    {
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

    /**
     * @return PurchaseVoucherDao
     */
    protected function getPurchaseVoucherDao()
    {
        return $this->createDao('Goods:PurchaseVoucherDao');
    }
}
