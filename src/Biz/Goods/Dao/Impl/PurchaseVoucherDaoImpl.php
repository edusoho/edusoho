<?php

namespace Biz\Goods\Dao\Impl;

use Biz\Goods\Dao\PurchaseVoucherDao;
use Codeages\Biz\Framework\Dao\AdvancedDaoImpl;

class PurchaseVoucherDaoImpl extends AdvancedDaoImpl implements PurchaseVoucherDao
{
    protected $table = 'goods_purchase_voucher';

    public function declares()
    {
        return [
            'conditions' => [
                'id= :id',
                'goodsId = :goodsId',
                'specsId = :specsId',
                'userId = :userId',
                'effectiveType = :effectiveType',
            ],
            'serializes' => [
            ],
            'timestamps' => [
                'createdTime',
                'updatedTime',
            ],
            'orderbys' => [
                'id',
                'createdTime',
                'updatedTime',
            ],
        ];
    }

    public function getBySpecsId($specsId)
    {
        return $this->getByFields(['specsId' => $specsId]);
    }

    public function findByIds($ids)
    {
        if (empty($ids)) {
            return [];
        }

        return $this->findInField('id', $ids);
    }
}
