<?php

namespace ApiBundle\Api\Resource\Good;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;

class GoodCourse extends AbstractResource
{
    /**
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $id)
    {
        $title = trim($request->query->get('title', ''));

        $goods = $this->getGoodsService()->getGoods($id);
        $product = $this->getProductService()->getProduct($goods['productId']);
        if ('classroom' !== $product['targetType']) {
            return [];
        }

        return $this->getClassroomCourses($product, $title);
    }

    private function getClassroomCourses($product, $title)
    {
        $apiRequest = new ApiRequest("/api/classrooms/{$product['targetId']}/courses", 'GET', ['title' => $title]);

        return ['classroomCourses' => $this->invokeResource($apiRequest)];
    }

    /**
     * @return GoodsService
     */
    private function getGoodsService()
    {
        return $this->service('Goods:GoodsService');
    }

    /**
     * @return ProductService
     */
    private function getProductService()
    {
        return $this->service('Product:ProductService');
    }
}
