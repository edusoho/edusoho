<?php

namespace Tests\Unit\S2B2C\Service;

use Biz\BaseTestCase;
use Biz\Course\Service\CourseSetService;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;

class CourseProductServiceTest extends BaseTestCase
{
    public function testSyncCourses()
    {
        $courseSetProductDetailJsonFile = $this->getContainer()->getParameter('kernel.root_dir').'/../tests/Unit/S2B2C/Fixtures/course_set_detail.json';
        $courseProductDetailJsonFile = $this->getContainer()->getParameter('kernel.root_dir').'/../tests/Unit/S2B2C/Fixtures/full_sync_data.json';
        $this->biz['qiQiuYunSdk.s2b2cService'] = $this->mockBiz(
            'qiQiuYunSdk.s2b2cService',
            [
                [
                    'functionName' => 'getDistributeContent',
                    'returnValue' => json_decode(file_get_contents($courseSetProductDetailJsonFile), true),
                    'withParams' => [1],
                ],
                [
                    'functionName' => 'getDistributeContent',
                    'returnValue' => json_decode(file_get_contents($courseProductDetailJsonFile), true),
                    'withParams' => [2],
                ],
            ]
        );

        $this->biz->offsetUnset('s2b2c.config');
        $this->biz->offsetSet('s2b2c.config', [
            'enabled' => true,
            'supplierId' => 1,
            'supplierDomain' => 'test.fenke.com',
            'businessMode' => 'dealer',
        ]);
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();

        $this->getS2B2CProductDao()->create([
            's2b2cProductDetailId' => 1,
            'supplierId' => $s2b2cConfig['supplierId'],
            'productType' => 'course_set',
            'remoteProductId' => 1,
            'remoteResourceId' => 84,
            'localResourceId' => 0,
        ]);

        $this->getS2B2CProductDao()->create([
            's2b2cProductDetailId' => 2,
            'supplierId' => $s2b2cConfig['supplierId'],
            'productType' => 'course',
            'remoteProductId' => 1,
            'remoteResourceId' => 84,
            'localResourceId' => 0,
        ]);
        $result = $this->getCourseProductService()->syncCourses(1);

        $this->assertEmpty($result);
    }

    public function testUpdateProductVersionData_withPurchaseNewCourse()
    {
        $this->mockUpdateProductVersionData('new');
        $this->getCourseProductService()->updateProductVersionData(76);

        $product = $this->getS2B2CProductService()->getProductBySupplierIdAndRemoteProductIdAndType(1, 76, 'course');
        $this->assertNotEmpty($product);
    }

    public function testUpdateProductVersionData_withExistCourse()
    {
        $this->mockUpdateProductVersionData('exist');
        $this->getCourseProductService()->updateProductVersionData(76);

        $product = $this->getS2B2CProductService()->getProductBySupplierIdAndRemoteProductIdAndType(1, 76, 'course');
        $this->assertNotEmpty($product);
    }

    /**
     * @expectedException \Exception
     */
    public function testUpdateProductVersionData_withExistCourseError()
    {
        $this->mockUpdateProductVersionData('exist');
        $this->biz['s2b2c.course_product_sync'] = $this->mockBiz(
            's2b2c.course_product_sync',
            [
                [
                    'functionName' => 'updateToLastedVersion',
                    'returnValue' => [],
                    'withParams' => [45],
                ],
            ]
        );
        $this->getCourseProductService()->updateProductVersionData(76);
    }

    protected function mockUpdateProductVersionData($type)
    {
        $courseSetProductDetailJsonFile = $this->getContainer()->getParameter('kernel.root_dir').'/../tests/Unit/S2B2C/Fixtures/course_set_detail.json';
        $courseProductDetailJsonFile = $this->getContainer()->getParameter('kernel.root_dir').'/../tests/Unit/S2B2C/Fixtures/full_sync_data.json';
        $this->biz['qiQiuYunSdk.s2b2cService'] = $this->mockBiz(
            'qiQiuYunSdk.s2b2cService',
            [
                [
                    'functionName' => 'getDistributeContent',
                    'returnValue' => json_decode(file_get_contents($courseSetProductDetailJsonFile), true),
                    'withParams' => [73],
                ],
                [
                    'functionName' => 'getDistributeContent',
                    'returnValue' => json_decode(file_get_contents($courseProductDetailJsonFile), true),
                    'withParams' => [81],
                ],
            ]
        );

        $this->biz->offsetUnset('s2b2c.config');
        $this->biz->offsetSet('s2b2c.config', [
            'enabled' => true,
            'supplierId' => 1,
            'supplierDomain' => 'test.fenke.com',
            'businessMode' => 'dealer',
        ]);

        $prepareCourseSet = [
            'syncStatus' => 'waiting',
            'sourceCourseSetId' => 77,
            'title' => '测试课程',
            'type' => 'normal',
            'sourceCourseId' => 77,
            'subtitle' => 'testSubtitle',
            'summary' => 'testSummary',
            'cover' => '',
            'maxCoursePrice' => '10',
            'minCoursePrice' => '10',
            'platform' => 'supplier',
        ];
        if ('new' === $type) {
            $newCourseSet = $this->getCourseSetService()->addCourseSet($prepareCourseSet);
        }
        if ('exist' === $type) {
            $newCourseSet = $this->getCourseSetService()->createCourseSet($prepareCourseSet);
        }

        $courseSetData = $this->getS2B2CProductDao()->create([
            's2b2cProductDetailId' => 73,
            'supplierId' => 1,
            'productType' => 'course_set',
            'remoteProductId' => 76,
            'remoteResourceId' => 77,
            'localResourceId' => $newCourseSet['id'],
        ]);
        $courseData = $this->getS2B2CProductDao()->create([
            's2b2cProductDetailId' => 81,
            'supplierId' => 1,
            'productType' => 'course',
            'remoteProductId' => 76,
            'remoteResourceId' => 77,
            'localResourceId' => $newCourseSet['defaultCourseId'],
        ]);
    }

    /**
     * @return CourseProductService
     */
    protected function getCourseProductService()
    {
        return $this->createService('S2B2C:CourseProductService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return ProductService
     */
    protected function getS2B2CProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }

    protected function getS2B2CProductDao()
    {
        return $this->getBiz()->dao('S2B2C:ProductDao');
    }
}
