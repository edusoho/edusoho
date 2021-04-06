<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;

class CourseSetsWithGoodsDataTag extends CourseBaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        if (empty($arguments['courseSets'])) {
            return [];
        }

        $goodsFields = [
            'ratingNum',
        ];

        return $this->courseSetsWithGoods($arguments['courseSets'], $goodsFields);
    }

    private function courseSetsWithGoods($courseSets, $goodsFields)
    {
        $products = $this->getProductService()->findProductsByTargetTypeAndTargetIds('course', ArrayToolkit::column($courseSets, 'id'));
        $products = ArrayToolkit::index($products, 'targetId');
        $goodss = $this->getGoodsService()->findGoodsByProductIds(ArrayToolkit::column($products, 'id'));
        $goodss = ArrayToolkit::index($goodss, 'productId');

        foreach ($courseSets as &$courseSet) {
            // todo: 2 个 if 可以合并为 $goods[$products[$courseSet['id']]['id']]
            if (empty($products[$courseSet['id']])) {
                continue;
            }

            $product = $products[$courseSet['id']];

            if (empty($goodss[$product['id']])) {
                continue;
            }
            $goods = $goodss[$product['id']];

            $courseSet = array_merge($courseSet, ArrayToolkit::parts($goods, $goodsFields));
        }

        return ArrayToolkit::index($courseSets, 'id');
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
