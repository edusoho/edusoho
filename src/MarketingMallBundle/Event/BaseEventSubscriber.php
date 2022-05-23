<?php

namespace MarketingMallBundle\Event;

use Codeages\Biz\Framework\Event\EventSubscriber;
use MarketingMallBundle\Biz\ProductMallGoodsRelation\Service\ProductMallGoodsRelationService;
use MarketingMallBundle\Client\MarketingMallClient;
use MarketingMallBundle\Common\GoodsContentBuilder\AbstractBuilder;

abstract class BaseEventSubscriber extends EventSubscriber
{
    protected function updateGoodsContent($type, AbstractBuilder $builder, $id)
    {
        $relation = $tihs->getProductMallGoodsRelationService() > getProductMallGoodsRelationByProductTypeAndProductId($type, $id);
        if (empty($relation)) {
            return;
        }

        $builder->setBiz($this->getBiz());
        $client = new MarketingMallClient($this->getBiz());
        $client->updateGoodsContent([
            'type' => $type,
            'body' => $builder->build($id),
        ]);
    }

    /**
     * @return ProductMallGoodsRelationService
     */
    protected function getProductMallGoodsRelationService()
    {
        return $this->getBiz()->service('MarketingMallBundle:ProductMallGoodsRelation:ProductMallGoodsRelationService');
    }
}
