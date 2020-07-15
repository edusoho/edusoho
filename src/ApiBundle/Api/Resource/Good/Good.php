<?php

namespace ApiBundle\Api\Resource\Good;

use ApiBundle\Api\Annotation\ApiConf;
use ApiBundle\Api\ApiRequest;
use ApiBundle\Api\Resource\AbstractResource;
use ApiBundle\Api\Util\AssetHelper;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Goods\Service\GoodsService;
use Biz\Product\Service\ProductService;
use Biz\System\Service\SettingService;

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
        return $this->buildGoodsData($id);
    }

    protected function buildGoodsData($goodsId)
    {
        $goods = $this->getGoodsService()->getGoods($goodsId);
        $product = $this->getProductService()->getProduct($goods['productId']);

        return [
            'id' => $goods['id'],
            'title' => $goods['title'],
            'subTitle' => $goods['subtitle'],
            'description' => $goods['summary'],
            'image' => AssetHelper::getFurl(empty($goods['images']['middle']) ? '' : $goods['images']['middle'], 'course.png'),
            'product' => [
                'targetType' => $product['targetType'],
                'targetId' => $product['targetId'],
            ],

            'hasExtension' => true,
            'extensions' => $this->collectGoodsExtensions($product),
            'specs' => $this->getGoodsSpecs($product['targetType'], $product['targetId']),
        ];
    }

    private function collectGoodsExtensions($product)
    {
        $defaultExtensions = [
            'teachers',
            'recommendGoods',
            'isFavorite',
        ];

        if ('classroom' === $product['targetType']) {
            $defaultExtensions = array_merge($defaultExtensions, ['classroomCourses']);
        }

        $goodsSetting = $this->getSettingService()->get('goods_setting', []);
        if (empty($goodsSetting['leading_join_enabled'])) {
            return $defaultExtensions;
        }

        return array_merge($defaultExtensions, ['mpQrCode']);
    }

    private function getGoodsSpecs($targetType, $targetId)
    {
        if ('course' != $targetType) {
            return [];
        }

        $courses = $this->getCourseService()->searchCourses(
            [
                'courseSetId' => $targetId,
                'status' => 'published',
            ],
            ['seq' => 'ASC', 'createdTime' => 'ASC'],
            0,
            PHP_INT_MAX
        );

        $specs = [];
        foreach ($courses as $course) {
            $specs[] = [
                'id' => $course['id'],
                'title' => $course['title'],
                'subTitle' => $course['subtitle'],
                'expiryMode' => $course['expiryMode'],
                'joinedNum' => $course['studentNum'],
                'price' => $course['price'],
                'isDefault' => $course['isDefault'],
                'services' => $this->buildGoodsServices($course['services']),
            ];
        }

        return $specs;
    }

    private function buildGoodsServices(array $serviceNames)
    {
        $allServices = [
            'homeworkReview' => [
                'code' => 'homeworkReview',
                'shortName' => 'site.services.homeworkReview.shortName',
                'fullName' => 'site.services.homeworkReview.fullName',
                'summary' => 'site.services.homeworkReview.summary',
                'active' => 0,
            ],
            'testpaperReview' => [
                'code' => 'testpaperReview',
                'shortName' => 'site.services.testpaperReview.shortName',
                'fullName' => 'site.services.testpaperReview.fullName',
                'summary' => 'site.services.testpaperReview.summary',
                'active' => 0,
            ],
            'teacherAnswer' => [
                'code' => 'teacherAnswer',
                'shortName' => 'site.services.teacherAnswer.shortName',
                'fullName' => 'site.services.teacherAnswer.fullName',
                'summary' => 'site.services.teacherAnswer.summary',
                'active' => 0,
            ],
            'liveAnswer' => [
                'code' => 'liveAnswer',
                'shortName' => 'site.services.liveAnswer.shortName',
                'fullName' => 'site.services.liveAnswer.fullName',
                'summary' => 'site.services.liveAnswer.summary',
                'active' => 0,
            ],
        ];

        $specifyServices = [];
        foreach ($serviceNames as $serviceName) {
            if (!empty($allServices[$serviceName])) {
                $specifyServices = array_merge($specifyServices, $allServices[$serviceName]);
            }
        }

        return $specifyServices;
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

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->service('Course:CourseService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->service('System:SettingService');
    }
}
