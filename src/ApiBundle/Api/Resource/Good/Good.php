<?php

namespace ApiBundle\Api\Resource\Good;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;

/**
 * Class Good Good并不合适,商品真实本体是Goods,单复数同形,类名为Good是为了满足接口的定义规范（带有s结尾的单词比较难处理）
 */
class Good extends AbstractResource
{
    /**
     * @param $id
     *
     * @return \string[][]
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $id)
    {
        return $this->mockGoodsData($id);
    }

    /**
     * @param $id
     *
     * @return []
     *            模拟的接口数据，商品主体数据
     */
    protected function mockGoodsData($id)
    {
        return [
            1 => [
                'description' => '计划无优惠，开启多服务',
            ],
        ];
    }
}
