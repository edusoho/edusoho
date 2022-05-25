<?php

namespace MarketingMallBundle\Event;

use Codeages\Biz\Framework\Event\EventSubscriber;
use MarketingMallBundle\Client\MarketingMallClient;
use MarketingMallBundle\Common\GoodsContentBuilder\AbstractBuilder;

abstract class BaseEventSubscriber extends EventSubscriber
{
    protected function updateGoodsContent($type, AbstractBuilder $builder, $id)
    {
        $builder->setBiz($this->getBiz());
        $client = new MarketingMallClient($this->getBiz());
        $client->updateGoodsContent([
            'type' => $type,
            'body' => $builder->build($id),
        ]);
    }
}
