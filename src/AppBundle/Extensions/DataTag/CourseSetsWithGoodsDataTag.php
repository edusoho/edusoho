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
        $courseSets = ArrayToolkit::index($courseSets, 'id');

        $products = $this->getProductService()->findProductsByTargetTypeAndTargetIds('course', ArrayToolkit::column($courseSets, 'id'));

        $products = ArrayToolkit::index($products, 'targetId');

        $courseSetsIndexByProductId = [];

        array_walk($courseSets, function ($value) use ($products, &$courseSetsIndexByProductId) {
            if (isset($products[$value['id']])) {
                $courseSetsIndexByProductId[$products[$value['id']]['id']] = $value;
            }
        });

        $goods = $this->getGoodsService()->findGoodsByProductIds(ArrayToolkit::column($products, 'id'));

        $goods = ArrayToolkit::index($goods, 'productId');

        foreach ($courseSetsIndexByProductId as $key => &$courseSet) {
            if (isset($goods[$key])) {
                array_walk($goodsFields, function ($value) use ($key, $goods,&$courseSet) {
                    $courseSet[$value] = $goods[$key][$value];
                });
            }
        }

        return $courseSetsIndexByProductId;
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
