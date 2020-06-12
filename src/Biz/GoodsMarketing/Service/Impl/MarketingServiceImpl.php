<?php

namespace Biz\GoodsMarketing\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\GoodsMarketing\Dao\MarketingMeansDao;
use Biz\GoodsMarketing\Service\MarketingService;

class MarketingServiceImpl extends BaseService implements MarketingService
{
    public function getMeans($id)
    {
        return $this->getMarketingMeansDao()->get($id);
    }

    public function createMeans($means)
    {
        if (!ArrayToolkit::requireds(
            $means,
            ['type', 'fromMeansId', 'targetType', 'targetId']
        )) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }
        $fields = ArrayToolkit::parts(
            $means,
            [
                'type',
                'productType',
                'fromMeansId',
                'targetType',
                'targetId',
                'status',
                'visibleOnGoodsPage',
            ]
        );

        return $this->getMarketingMeansDao()->create($fields);
    }

    public function updateMeans($id, $means)
    {
        $means = ArrayToolkit::parts(
            $means,
            [
                'status',
                'visibleOnGoodsPage',
            ]
        );

        return $this->getMarketingMeansDao()->update($id, $means);
    }

    public function findValidMeansByTargetTypeAndTargetId($targetType, $targetId)
    {
        return $this->getMarketingMeansDao()->findValidMeansByTargetTypeAndTargetId($targetType, $targetId);
    }

    public function searchMeans($conditions, $orderBys, $start, $limit, $columns = [])
    {
        return $this->getMarketingMeansDao()->search($conditions, $orderBys, $start, $limit, $columns);
    }

    public function countMeans($conditions)
    {
        return $this->getMarketingMeansDao()->count($conditions);
    }

    public function deleteMeans($id)
    {
        return $this->getMarketingMeansDao()->delete($id);
    }

    /**
     * @return MarketingMeansDao
     */
    protected function getMarketingMeansDao()
    {
        return $this->createDao('GoodsMarketing:MarketingMeansDao');
    }
}
