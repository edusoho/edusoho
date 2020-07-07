<?php

namespace Tests\Unit\S2B2C\Service;

use ApiBundle\Api\Resource\SyncProductNotify\NotifyEvent;
use Biz\BaseTestCase;
use Biz\Course\Dao\CourseChapterDao;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Service\CourseService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\SupplierProductNotifyService;
use Biz\System\Service\SettingService;

class SupplierProductNotifyServiceTest extends BaseTestCase
{
    public function testSyncSupplierProductEvent_withModifyPrice()
    {
        $this->biz->offsetUnset('s2b2c.config');
        $this->biz->offsetSet('s2b2c.config', [
            'enabled' => true,
            'supplierId' => 1,
            'supplierDomain' => 'test.fenke.com',
            'businessMode' => 'dealer',
        ]);
        $courseSet = $this->mockCourseSet();
        $course = $this->mockCourse(['courseSetId' => $courseSet['id']]);
        $savedProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields(['remoteResourceId' => 1, 'localResourceId' => $course['id']]));

        $modifyPriceEvent = new NotifyEvent([
            'productId' => $savedProduct['remoteResourceId'],
            'event' => 'modifyPrice',
            'data' => [
                'new' => [
                'suggestionPrice' => '20.00',
                'cooperationPrice' => '30.00',
            ], ],
        ]);

        $this->getSupplierNotifyService()->syncSupplierProductEvent($modifyPriceEvent);
        $product = $this->getS2B2CProductService()->getProduct($savedProduct['id']);
        $this->assertEquals('20.00', $product['suggestionPrice']);
        $this->assertEquals('30.00', $product['cooperationPrice']);
    }

    public function testSyncSupplierProductEvent_withCloseTaskEvent()
    {
        $this->biz->offsetUnset('s2b2c.config');
        $this->biz->offsetSet('s2b2c.config', [
            'enabled' => true,
            'supplierId' => 1,
            'supplierDomain' => 'test.fenke.com',
            'businessMode' => 'dealer',
        ]);
        $courseSet = $this->mockCourseSet();
        $course = $this->mockCourse(['courseSetId' => $courseSet['id']]);
        $savedProduct = $this->getS2B2CProductService()->createProduct($this->mockProductFields(['localResourceId' => $course['id']]));
        $lesson = $this->mockChapter($course['id'], '测试');
        $modifyPriceEvent = new NotifyEvent([
            'productId' => $savedProduct['remoteResourceId'],
            'event' => 'closeTask',
            'data' => [
                'taskId' => $lesson['syncId'],
            ],
        ]);
        $result = $this->getSupplierNotifyService()->syncSupplierProductEvent($modifyPriceEvent);
        $lesson = $this->getCourseChapterDao()->get($lesson['id']);
        $this->assertEquals('unpublished', $lesson['status']);
        $this->assertTrue($result);
    }

    private function mockCourseSet($fields = [])
    {
        $defaultFields = [
            'type' => 'course',
            'title' => 'hmm',
            'subtitle' => 'oh',
            'status' => 'draft',
            'serializeMode' => 'none',
            'ratingNum' => 1,
            'rating' => 1,
            'noteNum' => 1,
            'studentNum' => 1,
        ];

        $fields = array_merge($defaultFields, $fields);

        return $this->getCourseSetDao()->create($fields);
    }

    private function mockCourse($fields = [])
    {
        $defaultFields = [
            'courseSetId' => 1,
            'title' => 'a',
            'address' => 'a',
        ];

        $fields = array_merge($defaultFields, $fields);

        return $this->getCourseDao()->create($fields);
    }

    protected function mockChapter($courseId, $title)
    {
        $fields = [
            'courseId' => $courseId,
            'title' => $title,
            'type' => 'lesson',
            'status' => 'published',
            'syncId' => 1,
        ];

        return $this->getCourseService()->createChapter($fields);
    }

    protected function mockProductFields($customFields = [])
    {
        return array_merge([
            'supplierId' => 1,
            'productType' => 'course',
            'remoteProductId' => 1,
            'remoteResourceId' => 1,
            'localResourceId' => 1,
            'cooperationPrice' => (float) 2.00,
            'suggestionPrice' => (float) 3.00,
            'localVersion' => 1,
        ], $customFields);
    }

    /**
     * @return SupplierProductNotifyService
     */
    protected function getSupplierNotifyService()
    {
        return $this->createService('S2B2C:SupplierProductNotifyService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return CourseSetDao
     */
    protected function getCourseSetDao()
    {
        return $this->createDao('Course:CourseSetDao');
    }

    /**
     * @return CourseDao
     */
    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }

    /**
     * @return CourseChapterDao
     */
    protected function getCourseChapterDao()
    {
        return $this->createDao('Course:CourseChapterDao');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return ProductService
     */
    protected function getS2B2CProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }
}
