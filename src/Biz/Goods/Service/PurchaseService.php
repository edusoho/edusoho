<?php

namespace Biz\Goods\Service;

interface PurchaseService
{
    public function getVoucher($id);

    public function createVoucher($voucher);

    public function updateVoucher($id, $voucher);

    public function deleteVoucher($id);

    public function countVouchers($conditions);

    public function searchVouchers($conditions, $orderBys, $start, $limit, $columns = []);

    public function findVouchersByIds($ids);
}
