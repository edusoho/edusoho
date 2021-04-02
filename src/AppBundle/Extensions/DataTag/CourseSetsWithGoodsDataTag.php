<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;

class CourseSetsWithGoodsDataTag extends CourseBaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        if (!isset($arguments['courseSet'])) {
            return [];
        }

        $targetType = 'course';

        $goodsFields = [
            'ratingNum',
        ];
        $courseSetsWithGoods = [];

        $product = $this->getProductService()->getProductByTargetIdAndType($arguments['courseSet']['id'], $targetType);

        if ($product) {
            $goods = $this->getGoodsService()->getGoodsByProductId($product['id']);
            $goodsMerge = [];
            foreach ($goodsFields as $field) {
                $goodsMerge[$field] = $goods[$field];
            }
            $courseSetsWithGoods = array_merge($arguments['courseSet'], $goodsMerge);
        }

        return $courseSetsWithGoods;
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->getServiceKernel()->getBiz()->service('Product:ProductService');
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->getServiceKernel()->getBiz()->service('Goods:GoodsService');
    }
}
