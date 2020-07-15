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
        $componentTypes = $request->query->get('componentTypes');
        if (empty($componentTypes)) {
            return [];
        }

        return $this->getComponentsByTypes($id, $componentTypes);
    }

    private function getComponentsByTypes($goodsId, array $types)
    {
        $goods = $this->getGoodsService()->getGoods($goodsId);
        $product = $this->getProductService()->getProduct($goods['productId']);

        $components = [];
        foreach ($types as $type) {
            if ('isFavorite' === $type) {
                $components['isFavorite'] = $this->getIsFavoriteComponent($product);
                continue;
            }

            if ('mpQrCode' === $type) {
                $components['mpQrCode'] = $this->getMpQrCodeComponent();
                continue;
            }

            if ('teachers' === $type) {
                $components['teachers'] = $this->getTeacherComponent($product);
            }

            if ('recommendGoods' === $type) {
            }

            if ('classroomCourses' === $type) {
                $components['classroomCourses'] = $this->getClassroomCourses($product);
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

        return [
            'title' => $goodsSetting['leading']['label'],
            'content' => $goodsSetting['leading']['description'],
            'imageUrl' => AssetHelper::getFurl($goodsSetting['leading']['qrcode'], 'default'),
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

    public function getClassroomCourses($product)
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
    protected function getGoodsService()
    {
        return $this->service('Goods:GoodsService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->service('Product:ProductService');
    }

    /**
     * @return FavoriteService
     */
    protected function getFavoriteService()
    {
        return $this->service('Favorite:FavoriteService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->service('Classroom:ClassroomService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->service('Course:CourseSetService');
    }
}
