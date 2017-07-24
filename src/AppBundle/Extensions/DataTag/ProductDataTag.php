<?php

namespace AppBundle\Extensions\DataTag;

class ProductDataTag extends BaseDataTag implements DataTag
{
    /**
     * 获取所有积分商品.
     *
     * 可传入的参数：
     *
     *   productId 必传　产品ID
     *
     * @param array $arguments 参数
     *
     * @return array 积分商品
     */
    public function getData(array $arguments)
    {
        if (empty($arguments['productId'])) {
            return array();
        }

        return $this->getProductService()->getProduct($arguments['productId']);
    }

    protected function getProductService()
    {
        return $this->getServiceKernel()->createService('RewardPoint:ProductService');
    }
}
