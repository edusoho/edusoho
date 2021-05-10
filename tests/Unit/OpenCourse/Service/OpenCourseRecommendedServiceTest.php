<?php

namespace Tests\Unit\OpenCourse\Service;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ReflectionUtils;
use Biz\BaseTestCase;
use Biz\Goods\GoodsEntityFactory;
use Biz\Goods\GoodsException;
use Biz\Goods\Service\GoodsService;
use Biz\OpenCourse\Service\OpenCourseRecommendedService;
use Biz\Product\Service\ProductService;

class OpenCourseRecommendedServiceTest extends BaseTestCase
{
    public function testDeleteBatchRecommendCourses()
    {
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $openCourse = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = [$course1['id'], $course2['id']];
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');
        $this->getCourseRecommendedService()->deleteBatchRecommend([$course1['id']]);
        $result = $this->getCourseRecommendedService()->searchRecommends([], [], 0, \PHP_INT_MAX);
        $this->assertEquals($course2['id'], $result[0]['id']);

        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $openCourse = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = [$course1['id'], $course2['id']];
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');
        $this->getCourseRecommendedService()->deleteBatchRecommend([]);
        $result = $this->getCourseRecommendedService()->searchRecommends([], [], 0, \PHP_INT_MAX);
        $this->assertEquals(3, count($result));

        $this->getCourseRecommendedService()->deleteBatchRecommend([$course1['id'], $course2['id']]);
        $result = $this->getCourseRecommendedService()->searchRecommends([], [], 0, \PHP_INT_MAX);
        $this->assertEquals(1, count($result));
    }

    public function testAddRecommendedCourses()
    {
        $courseSet1 = $this->createCourse('test1');
        $courseSet2 = $this->createCourse('test2');
        list($product1, $goods1) = $this->getProductAndGoods($courseSet1);
        list($product2, $goods2) = $this->getProductAndGoods($courseSet2);

        $openCourse = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = [$goods1['id'], $goods2['id']];
        $this->getCourseRecommendedService()->addRecommendGoods($openCourse['id'], $recommendCourseIds1);

        $recommendCourseIds2 = [$goods1['id']];
        $this->getCourseRecommendedService()->addRecommendGoods($openCourse['id'], $recommendCourseIds2);
        $openCoursesRecommends = ArrayToolkit::index($this->getCourseRecommendedService()->findRecommendedGoodsByOpenCourseId($openCourse['id']), 'recommendGoodsId');

        $this->assertEquals(2, count($openCoursesRecommends));
        $this->assertEquals($openCourse['id'], $openCoursesRecommends[$goods1['id']]['openCourseId']);
        $this->assertEquals($openCourse['id'], $openCoursesRecommends[$goods2['id']]['openCourseId']);
        $this->assertEquals($goods1['id'], $openCoursesRecommends[$goods1['id']]['recommendGoodsId']);
        $this->assertEquals($goods2['id'], $openCoursesRecommends[$goods2['id']]['recommendGoodsId']);

        $result = $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], [], 'course');
        $this->assertTrue($result);
    }

    public function testUpdateOpenCourseRecommendedCourses()
    {
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $course3 = $this->createCourse('test3');
        $openCourse = $this->createOpenCourse('openCourse1');
        $recommendCourseIds = [$course1['id'], $course2['id'], $course3['id']];
        $recommendCourses = $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds, 'course');

        $activiteRecommendIds = [$recommendCourses[0]['id']];
        $this->getCourseRecommendedService()->updateOpenCourseRecommendedCourses($openCourse['id'], $activiteRecommendIds);

        $recommendeds = $this->getCourseRecommendedService()->findRecommendedGoodsByOpenCourseId($openCourse['id']);

        $this->assertEquals(1, count($recommendeds));

        $this->getCourseRecommendedService()->updateOpenCourseRecommendedCourses($openCourse['id'], []);
        $recommendeds = $this->getCourseRecommendedService()->findRecommendedGoodsByOpenCourseId($openCourse['id']);
        $this->assertEmpty($recommendeds);
    }

    public function testFindRecommendedCoursesByOpenCourseId()
    {
        $course1 = $this->createCourse('test1');
        $openCourse = $this->createOpenCourse('openCourse');
        $recommendCourseIds = [$course1['id']];
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds, 'normal');
        $recommendCourse1 = $this->getCourseRecommendedService()->findRecommendedGoodsByOpenCourseId($openCourse['id']);

        $this->assertEquals($openCourse['id'], $recommendCourse1[0]['openCourseId']);
        $this->assertEquals($course1['id'], $recommendCourse1[0]['recommendCourseId']);
    }

    public function testSearchRecommendCount()
    {
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $openCourse = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = [$course1['id'], $course2['id']];
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');

        $recommendedCourseCount = $this->getCourseRecommendedService()->countRecommends(['courseId' => $openCourse['id']]);

        $this->assertEquals(2, $recommendedCourseCount);
    }

    public function testSearchRecommends()
    {
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        $openCourse = $this->createOpenCourse('录播公开课');
        $recommendCourseIds1 = [$course1['id'], $course2['id']];
        $this->getCourseRecommendedService()->addRecommendedCourses($openCourse['id'], $recommendCourseIds1, 'course');

        $recommendedCourses = $this->getCourseRecommendedService()->searchRecommends(['courseId' => $openCourse['id']], ['createdTime' => 'DESC'], 0, 2);
        $recommendedCourses = ArrayToolkit::index($recommendedCourses, 'id');

        $this->assertEquals(2, count($recommendedCourses));
        $this->assertEquals($course2['id'], $recommendedCourses[$course2['id']]['recommendCourseId']);
        $this->assertEquals($course1['id'], $recommendedCourses[$course1['id']]['recommendCourseId']);
    }

    public function testRecommendedCoursesSort()
    {
        $course1 = $this->createCourse('test1');
        $course2 = $this->createCourse('test2');
        list($product1, $goods1) = $this->getProductAndGoods($course1);
        list($product2, $goods2) = $this->getProductAndGoods($course2);
        $openCourse = $this->createOpenCourse('录播公开课');
        $recommendGoodsIds1 = [$goods1['id'], $goods2['id']];
        $this->getCourseRecommendedService()->addRecommendGoods($openCourse['id'], $recommendGoodsIds1);

        $recommendCourses = $this->getCourseRecommendedService()->findRecommendedGoodsByOpenCourseId($openCourse['id']);
        $recommendCourses = ArrayToolkit::index($this->getCourseRecommendedService()->recommendedGoodsSort($recommendCourses), 'recommendGoodsId');

        $this->assertEquals($course1['title'], $recommendCourses[$goods1['id']]['goods']['title']);
        $this->assertEquals($course2['title'], $recommendCourses[$goods2['id']]['goods']['title']);
    }

    public function testFindRandomRecommendCourses()
    {
        $openCourse = $this->createOpenCourse('公开课1');
        $goodsIds = [];
        foreach (range(1, 10) as $i) {
            $course = $this->createCourse('course'.$i);
            list($product, $goods) = $this->getProductAndGoods($course);
            $goodsIds[] = $goods['id'];
        }
        $this->getCourseRecommendedService()->addRecommendGoods($openCourse['id'], $goodsIds);
        $needNum = 5;
        $randomCourses = $this->getCourseRecommendedService()->findRandomRecommendGoods($openCourse['id'], $needNum);

        $this->assertEquals(count($randomCourses), $needNum);
    }

    /**
     * @expectedException \Biz\Common\CommonException
     */
    public function testFindRandomRecommendCoursesError()
    {
        $this->getCourseRecommendedService()->findRandomRecommendGoods(1, -1);
    }

    public function testDeleteRecommendCourse()
    {
        $result = $this->getCourseRecommendedService()->deleteRecommend(1);
        $this->assertTrue($result);

        $fields = [
            'recommendCourseId' => 1,
            'recommendGoodsId' => 1,
            'openCourseId' => 1,
            'type' => 'normal',
        ];
        $recommendCourse = $this->getRecommendedCourseDao()->create($fields);
        $this->assertNotNull($recommendCourse);

        $this->getCourseRecommendedService()->deleteRecommend($recommendCourse['id']);
        $result = $this->getCourseRecommendedService()->getRecommendedCourseByCourseIdAndType(1, $recommendCourse['id'], 'normal');

        $this->assertNull($result);
    }

    public function testAddRecommendeds()
    {
        $recommendedCourseDao = $this->mockBiz(
            'OpenCourse:OpenCourseRecommendedDao',
            [
                [
                    'functionName' => 'create',
                    'withParamms' => [
                        [
                            'recommendCourseId' => 12,
                            'openCourseId' => 123,
                            'type' => 'live',
                        ],
                    ],
                    'times' => 1,
                ],
            ]
        );

        $result = ReflectionUtils::invokeMethod(
            $this->getCourseRecommendedService(),
            'addRecommendeds',
            [[12], 123, 'live']
        );
        $this->assertTrue($result);
        $recommendedCourseDao->shouldHaveReceived('create')->times(1);
    }

    protected function createCourse($title)
    {
        $course = [
            'title' => $title,
            'type' => 'normal',
            'courseSetId' => '1',
            'expiryMode' => 'forever',
            'learnMode' => 'freeMode',
        ];
        $createCourse = $this->getCourseSetService()->createCourseSet($course);

        return $createCourse;
    }

    protected function createOpenCourse($title)
    {
        $course = [
            'title' => $title,
            'type' => 'open',
        ];

        $createCourse = $this->getOpenCourseService()->createCourse($course);

        return $createCourse;
    }

    protected function getProductAndGoods($courseSet)
    {
        $existProduct = $this->getProductService()->getProductByTargetIdAndType($courseSet['id'], 'course');
        if (empty($existProduct)) {
            return [[], []];
        }

        $existGoods = $this->getGoodsService()->getGoodsByProductId($existProduct['id']);
        if (empty($existGoods)) {
            throw GoodsException::GOODS_NOT_FOUND();
        }

        return [$existProduct, $existGoods];
    }

    protected function getRecommendedCourseDao()
    {
        return $this->createDao('OpenCourse:OpenCourseRecommendedDao');
    }

    /**
     * @return OpenCourseRecommendedService
     */
    protected function getCourseRecommendedService()
    {
        return $this->createService('OpenCourse:OpenCourseRecommendedService');
    }

    protected function getOpenCourseService()
    {
        return $this->createService('OpenCourse:OpenCourseService');
    }

    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return GoodsEntityFactory
     */
    public function getGoodsEntityFactory()
    {
        return $this->biz['goods.entity.factory'];
    }

    /**
     * @return GoodsService
     */
    protected function getGoodsService()
    {
        return $this->biz->service('Goods:GoodsService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->biz->service('Product:ProductService');
    }
}
