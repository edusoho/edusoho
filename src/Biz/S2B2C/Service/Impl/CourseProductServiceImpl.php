<?php

namespace Biz\S2B2C\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\CourseException;
use Biz\Course\CourseSetException;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\LessonService;
use Biz\S2B2C\Dao\CourseChapterDao;
use Biz\S2B2C\Dao\ProductDao;
use Biz\S2B2C\S2B2CProductException;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\CacheService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Monolog\Logger;

/**
 * Class CourseProductServiceImpl
 */
class CourseProductServiceImpl extends BaseService implements CourseProductService
{
    const SYNC_STATUS_FINISHED = 'finished';

    const SYNC_STATUS_ERROR = 'error';

    /**
     * @param $s2b2cProductId
     *
     * @return bool
     *
     * @throws
     * 更新课程的计划列表
     */
    public function syncCourses($s2b2cProductId)
    {
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();

        if (empty($s2b2cConfig['supplierId'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $waitSyncProducts = $this->getS2B2CProductDao()->findBySupplierIdAndRemoteProductId($s2b2cConfig['supplierId'], $s2b2cProductId);
        if (empty($waitSyncProducts)) {
            return true;
        }

        $courseSets = array_filter($waitSyncProducts, function ($product) {
            return 'course_set' == $product['productType'];
        });
        $courseSetProduct = ArrayToolkit::get(array_values($courseSets), 0, []);

        $content = $this->getDistributeContent($courseSetProduct['s2b2cProductDetailId']);
        if (empty($content)) {
            return false;
        }
        $newCourseSet = $this->getCourseSetService()->addCourseSet($this->prepareCourseSetData($content));
        $this->getProductService()->updateProduct($courseSetProduct['id'], ['localResourceId' => $newCourseSet['id'], 'syncStatus' => self::SYNC_STATUS_FINISHED]);

        $courseProducts = array_filter($waitSyncProducts, function ($product) {
            return 'course' == $product['productType'];
        });
        $courseHasDefaultCourse = false;
        foreach ($courseProducts as $product) {
            $course = $this->getDistributeContent($product['s2b2cProductDetailId']);
            $course['courseSetId'] = $newCourseSet['id'];
            $course['courseSetTitle'] = $newCourseSet['title'];
            $course['platform'] = 'supplier';
            $localCourse = $this->getCourseService()->createCourse($course);

            $this->getProductService()->updateProduct($product['id'], [
                'localResourceId' => $localCourse['id'],
                'localVersion' => $course['editVersion'],
                'suggestionPrice' => $course['suggestionPrice'],
                'cooperationPrice' => $course['cooperationPrice'],
                'remoteResourceId' => $course['id'],
                'syncStatus' => self::SYNC_STATUS_FINISHED,
                ]);

            $this->biz['s2b2c.course_product_sync']->sync($course, ['syncCourseId' => $localCourse['id']]);

            if ($course['isDefault']) {
                // 如果供应商原课程中有默认计划，则直接同步
                $courseHasDefaultCourse = true;
                $this->getCourseSetService()->updateDefaultCourseId($newCourseSet['id'], $localCourse['id']);
            }
        }

        // 如果供应商原课程中没有默认计划，则按默认规则生成
        if (!$courseHasDefaultCourse) {
            $this->getCourseSetService()->updateCourseSetDefaultCourseId($newCourseSet['id']);
        }
    }

    protected function getDistributeContent($s2b2cProductDetailId)
    {
        $content = $this->getS2B2CFacadeService()->getS2B2CService()->getDistributeContent($s2b2cProductDetailId);
        if (!empty($content['status']) && 'success' == $content['status']) {
            return $content['data'];
        }

        return null;
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

    public function updateProductVersionData($s2b2cProductId)
    {
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();

        if (empty($s2b2cConfig['supplierId'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $waitSyncProducts = $this->getS2B2CProductDao()->findBySupplierIdAndRemoteProductId($s2b2cConfig['supplierId'], $s2b2cProductId);
        if (empty($waitSyncProducts)) {
            return true;
        }

        $courseSets = array_filter($waitSyncProducts, function ($product) {
            return 'course_set' == $product['productType'];
        });
        $courseSetProduct = ArrayToolkit::get(array_values($courseSets), 0, []);

        $newCourseSet = $this->getCourseSetService()->getCourseSet($courseSetProduct['localResourceId']);

        $this->getProductService()->updateProduct($courseSetProduct['id'], ['localResourceId' => $newCourseSet['id'], 'syncStatus' => self::SYNC_STATUS_FINISHED]);

        $courseProducts = array_filter($waitSyncProducts, function ($product) {
            return 'course' == $product['productType'];
        });
        foreach ($courseProducts as $courseProduct) {
            if (0 == $courseProduct['localResourceId']) {
                //新计划进行同步
                $this->syncNewCourse($courseProduct, $newCourseSet);
                continue;
            }

            if (!$this->updateCourseVersionData($courseProduct)) {
                $this->getLogger()->error('更新失败，productId#'.$courseProduct['id']);
                $this->createNewException(S2B2CProductException::UPDATE_PRODUCT_VERSION_FAIL());
            }
        }

        $this->getProductService()->updateProduct($courseSetProduct['id'], ['localVersion' => $courseSetProduct['remoteVersion'], 'changelog' => []]);

        return true;
    }

    protected function syncNewCourse($product, $courseSet)
    {
        $course = $this->getDistributeContent($product['s2b2cProductDetailId']);
        $course['courseSetId'] = $courseSet['id'];
        $course['courseSetTitle'] = $courseSet['title'];
        $course['platform'] = 'supplier';
        $localCourse = $this->getCourseService()->createCourse($course);

        $this->getProductService()->updateProduct($product['id'], [
            'localResourceId' => $localCourse['id'],
            'localVersion' => $course['editVersion'],
            'suggestionPrice' => $course['suggestionPrice'],
            'cooperationPrice' => $course['cooperationPrice'],
            'remoteResourceId' => $course['id'],
            'syncStatus' => self::SYNC_STATUS_FINISHED,
        ]);

        $this->biz['s2b2c.course_product_sync']->sync($course, ['syncCourseId' => $localCourse['id']]);
    }

    protected function updateCourseVersionData($product)
    {
        $course = $this->getCourseService()->getCourse($product['localResourceId']);
        if (empty($course)) {
            return false;
        }
        try {
            $this->beginTransaction();
            $sourceCourse = $this->getDistributeContent($product['s2b2cProductDetailId']);
            if ($product['localVersion'] >= $sourceCourse['editVersion']) {
                $this->getLogger()->info("课程 - {$sourceCourse['courseSetTitle']}(courseSetId#{$sourceCourse['courseSetId']}) 版本已经是最新，无需处理", ['nowVersion' => $product['localVersion'], 'sourceVersion' => $sourceCourse['editVersion']]);
                $this->commit();

                return true;
            }
            $this->biz['s2b2c.course_product_sync']->updateToLastedVersion($sourceCourse, ['syncCourseId' => $product['localResourceId']]);
            $this->getProductService()->updateProduct($product['id'], ['localVersion' => $sourceCourse['editVersion'], 'changelog' => []]);
            $this->getLogService()->info('course', 'update', "(#{$course['id']})《{$sourceCourse['courseSetTitle']}》更新版本到最新成功,版本变动：V{$product['localVersion']}->V{$sourceCourse['editVersion']}", ['userId' => $this->getCurrentUser()->getId()]);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLogger()->error("[syncCourseProduct] 更新课程到最新 - {$course['courseSetTitle']}(courseSetId#{$course['courseSetId']}) 失败", ['message' => $e->getMessage(), 'errorFile' => $e->getFile().$e->getLine(), 'error' => $e->getTraceAsString()]);
            $this->getLogService()->error('course', 'update', "(#{$course['id']})《{$course['courseSetTitle']}》更新版本到最新失败，版本无变化：V{$product['localVersion']}", ['userId' => $this->getCurrentUser()->getId()]);

            return false;
        }

        return true;
    }

    /**
     * @param $products
     * 下架对应商品
     * @codeCoverageIgnore
     */
    public function closeProducts($products)
    {
        foreach ($products as $product) {
            if ('closed' != $product['syncStatus']) {
                $this->getLogger()->info("[closeSupplierCourseProduct] 开始关闭采购商品#{$product['id']}");
                try {
                    if ('course' == $product['productType']) {
                        $this->getCourseDao()->update($product['localResourceId'], ['status' => 'closed']);
                    }

                    if ('course_set' == $product['productType']) {
                        $this->getCourseSetDao()->update($product['localResourceId'], ['status' => 'closed']);
                    }
                } catch (\Exception $e) {
                    $this->getLogger()->info("[closeSupplierCourseProduct] 关闭具体商品类型{$product['productType']}#{$product['localResourceId']}，操作出现问题，忽略");
                }
                $this->getLogger()->info("[closeSupplierCourseProduct] 关闭采购商品#{$product['id']}，操作成功");
            }
        }
    }

    /**
     * @param $s2b2cProductId
     * @param $remoteCourseId
     * @param $priceFields
     *
     * @return bool
     *
     * @throws \Exception
     * @codeCoverageIgnore
     */
    public function syncProductPrice($s2b2cProductId, $remoteCourseId, $priceFields)
    {
        $this->beginTransaction();
        try {
            $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();

            $product = $this->getProductService()->getByProductIdAndRemoteResourceIdAndType($s2b2cProductId, $remoteCourseId, 'course');

            if (empty($product)) {
                $this->createNewException(CourseSetException::SOURCE_COURSE_NOTFOUND());
            }

            $product = $this->getProductService()->updateProduct($product['id'], ArrayToolkit::parts($priceFields, ['suggestionPrice', 'cooperationPrice']));
            $course = $this->getCourseService()->getCourse($product['localResourceId']);

            $this->biz->offsetGet('s2b2c.merchant.logger')->info('[syncProductPrice] $merchantSetting', $s2b2cConfig);

            if (isset($s2b2cConfig['businessMode']) && S2B2CFacadeService::FRANCHISEE_MODE == $s2b2cConfig['businessMode']) {
                $this->getCourseService()->updateCourseMarketing($course['id'], array_merge($course, ['originPrice' => $priceFields['suggestionPrice']]));
            }
            $this->commit();

            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLogger()->error('[syncProductPriceError]'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * @param $s2b2cProductId
     * @param $remoteCourseId
     *
     * @return bool|mixed
     *
     * @throws \Exception
     * @codeCoverageIgnore
     */
    public function closeCourse($s2b2cProductId, $remoteCourseId)
    {
        $this->beginTransaction();
        try {
            $product = $this->getProductService()->getByProductIdAndRemoteResourceIdAndType($s2b2cProductId, $remoteCourseId, 'course');

            if (empty($product)) {
                $this->createNewException(CourseSetException::SOURCE_COURSE_NOTFOUND());
            }

            $this->getCourseService()->closeCourse($product['localResourceId']);
            $this->commit();

            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLogger()->error('[syncCloseCourse]'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * @param $s2b2cProductId
     * @param $remoteCourseSetId
     *
     * @return bool|mixed
     *
     * @throws \Exception
     * @codeCoverageIgnore
     */
    public function closeCourseSet($s2b2cProductId, $remoteCourseSetId)
    {
        $this->beginTransaction();
        try {
            $product = $this->getProductService()->getByProductIdAndRemoteResourceIdAndType($s2b2cProductId, $remoteCourseSetId, 'course_set');

            if (empty($product)) {
                $this->createNewException(CourseSetException::SOURCE_COURSE_NOTFOUND());
            }

            $this->getCourseSetService()->closeCourseSet($product['localResourceId']);
            $this->commit();

            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLogger()->error('[synccloseCourseSet]'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * @param $s2b2cProductId
     * @param $remoteResourceId
     * @param $lessonId
     *
     * @return bool|mixed
     *
     * @throws \Exception
     * @codeCoverageIgnore
     */
    public function closeTask($s2b2cProductId, $remoteResourceId, $lessonId)
    {
        $this->beginTransaction();
        try {
            $product = $this->getProductService()->getByProductIdAndRemoteResourceIdAndType($s2b2cProductId, $remoteResourceId, 'course');
            if (empty($product)) {
                $this->createNewException(CourseSetException::SOURCE_COURSE_NOTFOUND());
            }
            $lesson = $this->getCourseChapterDao()->getByCourseIdAndSyncId($product['localResourceId'], $lessonId);

            if (empty($lesson) || 'lesson' != $lesson['type']) {
                $this->getLogger()->error('不存在的课时 lessonSyncId:'.$lessonId.' lesson'.json_encode($lesson), $product);
                //todo 目前无法通过版本判断合适加入的task 暂时返回true
                return true;
            }

            if ('unpublished' == $lesson['status']) {
                $this->getLogger()->error('课时已经下架，无需操作 lessonSyncId:'.$lessonId);

                return true;
            }

            $result = $this->getCourseLessonService()->unpublishLesson($lesson['courseId'], $lesson['id']);

            $this->getLogger()->info('unPublishTask: '.json_encode($result));
            $this->commit();

            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLogger()->error('[syncProductPriceError]'.$e->getMessage());
            throw $e;
        }
    }

    /**
     * @param $courseSet
     *
     * @return array|bool|null[]|string[]
     * @codeCoverageIgnore
     */
    public function deleteProductsByCourseSet($courseSet)
    {
        if ('supplier' != $courseSet['platform']) {
            return true;
        }

        try {
            $this->beginTransaction();
            $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);
            $courseSetProduct = $this->getProductService()->getByTypeAndLocalResourceId('course_set', $courseSet['id']);
            $courseProducts = $this->getProductService()->findProductsBySupplierIdAndProductTypeAndLocalResourceIds($courseSetProduct['supplierId'], 'course', ArrayToolkit::column($courses, 'id'));

            $this->getProductService()->deleteProduct($courseSetProduct['id']);
            $this->getProductService()->deleteByIds(ArrayToolkit::column($courseProducts, 'id'));
            $result = $this->getS2B2CFacadeService()->getS2B2CService()->changePurchaseStatusToRemoved($courseSetProduct['remoteProductId']);
            if (!isset($result['status']) || 'success' != $result['status']) {
                $this->createNewException(S2B2CProductException::REMOVE_PRODUCT_FAILED());
            }

            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLogger()->error(sprintf('[deleteProductsByCourseSetError] %s', $e->getMessage()));
            $this->createNewException(S2B2CProductException::REMOVE_PRODUCT_FAILED());
        }

        return true;
    }

    /**
     * @param $localCourseId
     *
     * @throws \Exception
     *                    检查计划是否有操作权限
     * @codeCoverageIgnore
     */
    public function checkCourseStatus($localCourseId)
    {
        $this->checkMerchantStatus();
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
        $courseProduct = $this->getProductService()->getProductBySupplierIdAndLocalResourceIdAndType($s2b2cConfig['supplierId'], $localCourseId, 'course');

        $sourceCourse = $this->getS2B2CFacadeService()->getS2B2CService()->getDistributeContent($courseProduct['s2b2cProductDetailId']);

        if (empty($sourceCourse['status'])) {
            $this->createNewException(CourseSetException::SOURCE_COURSE_NOTFOUND());
        }
        $sourceCourse = $sourceCourse['data'];
        if ('published' != $sourceCourse['status']) {
            $this->createNewException(CourseException::SOURCE_COURSE_CLOSED());
        }
    }

    /**
     * @param $localCourseSetId
     *
     * @throws \Exception
     *                    检查课程是否有操作权限
     * @codeCoverageIgnore
     */
    public function checkCourseSetStatus($localCourseSetId)
    {
        $this->checkMerchantStatus();
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
        $courseSetProduct = $this->getProductService()->getProductBySupplierIdAndLocalResourceIdAndType($s2b2cConfig['supplierId'], $localCourseSetId, 'course_set');
        $sourceCourseSet = $this->getS2B2CFacadeService()->getS2B2CService()->getDistributeContent($courseSetProduct['s2b2cProductDetailId']);

        if (empty($sourceCourseSet['status'])) {
            $this->createNewException(CourseSetException::SOURCE_COURSE_NOTFOUND());
        }

        $sourceCourseSet = $sourceCourseSet['data'];
        if ('published' != $sourceCourseSet['status']) {
            $this->createNewException(CourseSetException::SOURCE_COURSE_CLOSED());
        }
    }

    /**
     * @throws \Exception
     * @codeCoverageIgnore
     */
    protected function checkMerchantStatus()
    {
        $merchant = $this->getS2B2CFacadeService()->getMe();
        if (!empty($merchant['error']) || empty($merchant['status'])) {
            $this->createNewException(CourseSetException::SOURCE_COURSE_NOTFOUND());
        }
        if ('cooperation' != $merchant['coop_status']) {
            $this->createNewException(CourseSetException::SOURCE_COURSE_CLOSED());
        }
    }

    protected function getLogger()
    {
        return $this->biz->offsetGet('s2b2c.merchant.logger');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->createService('System:LogService');
    }

    /**
     * @return Logger
     */
    protected function getS2B2CLogger()
    {
        return $this->biz->offsetGet('s2b2c.merchant.logger');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->biz->service('S2B2C:ProductService');
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
     * @return CacheService
     */
    protected function getCacheService()
    {
        return $this->createService('System:CacheService');
    }

    /**
     * @return CourseDao
     *                   不推荐直接调用CourseDao
     */
    protected function getCourseDao()
    {
        return $this->createDao('Course:CourseDao');
    }

    /**
     * @return LessonService
     */
    protected function getCourseLessonService()
    {
        return $this->createService('Course:LessonService');
    }

    /**
     * @return CourseChapterDao
     */
    protected function getCourseChapterDao()
    {
        return $this->createDao('S2B2C:CourseChapterDao');
    }

    /**
     * @return ProductService
     */
    protected function getS2B2CProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }

    /**
     * @return ProductDao
     */
    protected function getS2B2CProductDao()
    {
        return $this->biz->dao('S2B2C:ProductDao');
    }
}
