<?php

namespace Biz\Goods\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Goods\Dao\GoodsDao;
use Biz\Goods\Service\GoodsService;

class GoodsServiceImpl extends BaseService implements GoodsService
{
    public function getGoods($id)
    {
        return $this->getGoodsDao()->get($id);
    }

    public function createGoods($goods)
    {
        if (!ArrayToolkit::requireds($goods, ['productId', 'title'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $goods = ArrayToolkit::parts($goods, ['productId', 'title', 'images']);

        return $this->getGoodsDao()->create($goods);
    }

    public function updateGoods($id, $goods)
    {
        $goods = ArrayToolkit::parts($goods, ['title', 'images']);

        return $this->getGoodsDao()->update($id, $goods);
    }

    public function deleteGoods($id)
    {
        return $this->getGoodsDao()->delete($id);
    }

    public function searchGoods($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getGoodsDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    /**
     * @return GoodsDao
     */
    protected function getGoodsDao()
    {
        return $this->createDao('Goods:GoodsDao');
    }
}
