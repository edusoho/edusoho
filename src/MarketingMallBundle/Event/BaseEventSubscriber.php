<?php

namespace MarketingMallBundle\Event;

use Codeages\Biz\Framework\Event\EventSubscriber;
use Codeages\Biz\Framework\Service\Exception\ServiceException;
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

    protected function deleteMallGoods($code)
    {
        try {
            $client = new MarketingMallClient($this->getBiz());
            $result = $client->deleteGoodsBycode($code);
            if (!$result['ok']) {
                throw new ServiceException('删除营销商城商品失败，请重试！');
            }
        } catch (\Exception $exception) {
            throw $exception;
        }
    }
}
