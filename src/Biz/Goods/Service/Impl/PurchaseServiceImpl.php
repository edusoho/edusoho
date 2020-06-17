<?php

namespace Biz\Goods\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Goods\Dao\PurchaseVoucherDao;
use Biz\Goods\Service\PurchaseService;

class PurchaseServiceImpl extends BaseService implements PurchaseService
{
    public function getVoucher($id)
    {
        return $this->getPurchaseVoucherDao()->get($id);
    }

    public function createVoucher($voucher)
    {
        if (!ArrayToolkit::requireds($voucher, ['specsId', 'goodsId', 'userId', 'effectiveType'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $voucher = ArrayToolkit::parts($voucher, [
            'specsId',
            'goodsId',
            'userId',
            'orderId',
            'effectiveType',
            'effectiveType',
            'effectiveTime',
            'invalidTime',
        ]);

        return $this->getPurchaseVoucherDao()->create($voucher);
    }

    public function updateVoucher($id, $voucher)
    {
        $voucher = ArrayToolkit::parts($voucher, [
            'specsId',
            'goodsId',
            'userId',
            'orderId',
            'effectiveType',
            'effectiveType',
            'effectiveTime',
            'invalidTime',
        ]);

        return $this->getPurchaseVoucherDao()->update($id, $voucher);
    }

    public function deleteVoucher($id)
    {
        return $this->getPurchaseVoucherDao()->delete($id);
    }

    public function countVouchers($conditions)
    {
        return $this->getPurchaseVoucherDao()->count($conditions);
    }

    public function searchVouchers($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getPurchaseVoucherDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function findVouchersByIds($ids)
    {
        return $this->getPurchaseVoucherDao()->findByIds($ids);
    }

    /**
     * @return PurchaseVoucherDao
     */
    protected function getPurchaseVoucherDao()
    {
        return $this->createDao('Goods:PurchaseVoucherDao');
    }
}
