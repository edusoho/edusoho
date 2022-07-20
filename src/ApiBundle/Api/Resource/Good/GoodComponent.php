<?php

namespace ApiBundle\Api\Resource\Good;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Resource\Filter;
use ApiBundle\Api\Resource\User\UserFilter;
use ApiBundle\Api\Util\AssetHelper;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseSetService;
use Biz\Favorite\Service\FavoriteService;
use Biz\Goods\Service\GoodsService;
use Biz\Goods\Service\RecommendGoodsService;
use Biz\Product\Service\ProductService;
use Biz\System\Service\SettingService;

class GoodComponent extends AbstractResource
{
    /**
     * @param $id
     * @param $component
     *
     * @ApiConf(isRequiredAuth=false)
     */
    public function get(ApiRequest $request, $id, $component)
    {
        return $this->getComponentsByTypes($id, [$component]);
    }

    /**
     * @param $id
     *
     * @return array
     * @ApiConf(isRequiredAuth=false)
     */
    public function search(ApiRequest $request, $id)
    {
        $componentTypes = ['mpQrCode', 'teachers', 'recommendGoods', 'classroomCourses'];

        return $this->getComponentsByTypes($id, $componentTypes);
    }

    private function getComponentsByTypes($goodsId, array $types)
    {
        $goods = $this->getGoodsService()->getGoods($goodsId);
        $product = $this->getProductService()->getProduct($goods['productId']);

        $components = [];
        foreach ($types as $type) {
            if ('mpQrCode' === $type) {
                $components['mpQrCode'] = $this->getMpQrCodeComponent();
                continue;
            }

            if ('teachers' === $type) {
                $components['teachers'] = $this->getTeacherComponent($product);
                continue;
            }

            if ('recommendGoods' === $type) {
                $components['recommendGoods'] = $this->getRecommendGoodsComponent($goods);
                foreach ($components['recommendGoods'] as &$good) {
                    $good = $this->getGoodsService()->convertGoodsPrice($good);
                }
                continue;
            }

            if ('classroomCourses' === $type) {
                $components['classroomCourses'] = $this->getClassroomCourses($product);
                continue;
            }
        }

        return $components;
    }

    private function getIsFavoriteComponent($product)
    {
        $favorite = $this->getFavoriteService()->getUserFavorite(
            $this->getCurrentUser()->getId(),
            $product['targetType'],
            $product['targetId']
        );

        return !empty($favorite);
    }

    private function getMpQrCodeComponent()
    {
        $goodsSetting = $this->getSettingService()->get('goods_setting', []);
        if (empty($goodsSetting['leading_join_enabled'])) {
            return null;
        }

        return [
            'title' => $goodsSetting['leading']['label'],
            'content' => $goodsSetting['leading']['description'],
            'imageUrl' => AssetHelper::getFurl($goodsSetting['leading']['qrcode']),
        ];
    }

    private function getTeacherComponent($product)
    {
        if ('course' === $product['targetType']) {
            $courseSet = $this->getCourseSetService()->getCourseSet($product['targetId']);
            if (empty($courseSet['teacherIds'])) {
                return [];
            }

            $teachers['teacherIds'] = $courseSet['teacherIds'];
            $this->getOCUtil()->single($teachers, ['teacherIds']);

            $userFilter = new UserFilter();
            $userFilter->setMode(Filter::SIMPLE_MODE);
            $userFilter->filters($teachers['teachers']);

            return $teachers['teachers'];
        }

        if ('classroom' === $product['targetType']) {
            $classroom = $this->getClassroomService()->getClassroom($product['targetId']);
            if (empty($classroom['headTeacherId'])) {
                return [];
            }

            $teachers['teacherIds'] = [$classroom['headTeacherId']];
            $this->getOCUtil()->single($teachers, ['teacherIds']);

            $userFilter = new UserFilter();
            $userFilter->setMode(Filter::SIMPLE_MODE);
            $userFilter->filters($teachers['teachers']);

            return $teachers['teachers'];
        }
    }

    private function getRecommendGoodsComponent($goods)
    {
        $recommendGoods = $this->getRecommendGoodsService()->findRecommendedGoodsByGoods($goods);

        $goodsFilter = new GoodFilter();
        $goodsFilter->setMode(Filter::SIMPLE_MODE);
        $goodsFilter->filters($recommendGoods);

        return $recommendGoods;
    }

    private function getClassroomCourses($product)
    {
        if ('classroom' !== $product['targetType']) {
            return [];
        }

        $apiRequest = new ApiRequest("/api/classrooms/{$product['targetId']}/courses", 'GET');

        return $this->invokeResource($apiRequest);
    }

    /**
     * @return GoodsService
     */
    private function getGoodsService()
    {
        return $this->service('Goods:GoodsService');
    }

    /**
     * @return RecommendGoodsService
     */
    private function getRecommendGoodsService()
    {
        return $this->service('Goods:RecommendGoodsService');
    }

    /**
     * @return ProductService
     */
    private function getProductService()
    {
        return $this->service('Product:ProductService');
    }

    /**
     * @return FavoriteService
     */
    private function getFavoriteService()
    {
        return $this->service('Favorite:FavoriteService');
    }

    /**
     * @return ClassroomService
     */
    private function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }
}
