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
        $fullSyncDataJsonFile = $this->getContainer()->getParameter('kernel.root_dir').'/../tests/Unit/S2B2C/Fixtures/full_sync_data.json';
        $supplierApi = \Mockery::mock($this->biz->offsetGet('supplier.platform_api'));
        $supplierApi->shouldReceive('getSupplierCourseSetProductDetail')->times(2)->andReturn(json_decode(file_get_contents($courseSetProductDetailJsonFile), true));
        $supplierApi->shouldReceive('getSupplierProductSyncData')->times(1)->andReturn(json_decode(file_get_contents($fullSyncDataJsonFile), true));

        $this->biz->offsetUnset('supplier.platform_api');
        $this->biz->offsetSet('supplier.platform_api', $supplierApi);
        $this->biz->offsetUnset('s2b2c.config');
        $this->biz->offsetSet('s2b2c.config', [
            'enabled' => true,
            'supplierId' => 1,
            'supplierDomain' => 'test.fenke.com',
            'businessMode' => 'dealer',
        ]);
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
        $courseSetData = $this->getS2B2CFacadeService()->getSupplierPlatformApi()->getSupplierCourseSetProductDetail(1);
        $prepareCourseSet = $this->prepareCourseSetData($courseSetData);
        $newCourseSet = $this->getCourseSetService()->addCourseSet($prepareCourseSet);
        $product = $this->getS2B2CProductService()->createProduct([
            'supplierId' => $s2b2cConfig['supplierId'],
            'productType' => 'course_set',
            'remoteProductId' => $courseSetData['s2b2cDistributeId'],
            'remoteResourceId' => $courseSetData['id'],
            'localResourceId' => $newCourseSet['id'],
        ]);
        $this->getCourseProductService()->syncCourses($newCourseSet, $product);

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

    protected function prepareCourseSetData($courseSetData)
    {
        return [
            'syncStatus' => 'waiting',
            'sourceCourseSetId' => $courseSetData['id'],
            'title' => $courseSetData['title'],
            'type' => $courseSetData['type'],
            'sourceCourseId' => $courseSetData['defaultCourseId'],
            'subtitle' => $courseSetData['subtitle'],
            'summary' => $courseSetData['summary'],
            'cover' => $courseSetData['cover'],
            'maxCoursePrice' => $courseSetData['maxCoursePrice'],
            'minCoursePrice' => $courseSetData['minCoursePrice'],
            'platform' => 'supplier',
        ];
    }
}