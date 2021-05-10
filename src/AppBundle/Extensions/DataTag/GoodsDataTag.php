<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Goods\Service\GoodsService;

class GoodsDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取一个Goods.
     *
     * 可传入的参数：
     *   id 必需 Goods ID
     *
     * @return array|null
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['id'])) {
            throw new \InvalidArgumentException($this->getServiceKernel()->trans('id参数缺失'));
        }

        $goods = $this->getGoodsService()->getGoods($arguments['id']);
        $goods['isVipRight'] = $this->getWebExtension()->isVipRight($goods['id'], $goods['type']);

        return $goods;
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->createService('Goods:GoodsService');
    }
}
