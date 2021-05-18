<?php

namespace ApiBundle\Api\Resource\ScrmGood;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use AppBundle\Common\ArrayToolkit;
use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;
use Symfony\Component\Routing\Generator\UrlGenerator;

class ScrmGood extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $id)
    {
        $goods = $this->getGoodsService()->getGoods($id);
        $this->getOCUtil()->single($goods, ['creator']);

        $goods['product'] = $this->getProduct($goods);
        $goods = $this->getGoodsService()->convertGoodsPrice($goods);
        $goodsEntity = $this->getGoodsService()->getGoodsEntityFactory()->create($goods['type']);

        $this->fetchSpecs($goods, $goodsEntity, $request);

        $this->getGoodsService()->hitGoods($goods['id']);

        return $goods;
    }

    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request)
    {
        $conditions = $this->filterConditions($request->query->all());
        $count = $this->getGoodsService()->countGoods($conditions);
        $sort = $this->getSort($request);
        list($offset, $limit) = $this->getOffsetAndLimit($request);
        $goodses = $this->getGoodsService()->searchGoods($conditions, $sort, $offset, $limit);

        foreach ($goodses as &$goods) {
            $goodsEntity = $this->getGoodsService()->getGoodsEntityFactory()->create($goods['type']);
            $this->fetchSpecs($goods, $goodsEntity, $request);
        }

        return $this->makePagingObject($goodses, $count, $offset, $limit);
    }

    private function getProduct($goods)
    {
        $product = $this->getProductService()->getProduct($goods['productId']);
        //获取状态中的组件
        $this->getOCUtil()->single($product, ['targetId'], 'course' == $product['targetType'] ? 'courseSet' : $product['targetType']);

        return $product;
    }

    private function fetchSpecs(&$goods, $goodsEntity, $request)
    {
        $goods['specs'] = $this->getGoodsService()->findPublishedGoodsSpecsByGoodsId($goods['id']);
        foreach ($goods['specs'] as &$spec) {
            $spec = $this->getGoodsService()->convertSpecsPrice($goods, $spec);
            $spec['learnUrl'] = 'course' === $goods['type']
                ? $this->generateUrl('my_course_show', ['id' => $spec['targetId']], UrlGenerator::ABSOLUTE_URL)
                : $this->generateUrl('classroom_show', ['id' => $spec['targetId']], UrlGenerator::ABSOLUTE_URL);

            $spec['teacherIds'] = $goodsEntity->getSpecsTeacherIds($goods, $spec);
        }
        $this->getOCUtil()->multiple($goods['specs'], ['teacherIds']);
    }

    protected function filterConditions($conditions)
    {
        $allowedConditions = [
            'type',
            'types',
            'title',
            'titleLike',
            'price',
            'status', //unpublished,published
        ];
        $conditions['status'] = 'published';

        return ArrayToolkit::parts($conditions, $allowedConditions);
    }

    /**
     * @return GoodsService
     */
    public function getGoodsService()
    {
        return $this->biz->service('Goods:GoodsService');
    }

    /**
     * @return ProductService
     */
    private function getProductService()
    {
        return $this->service('Product:ProductService');
    }
}
