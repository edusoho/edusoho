<?php

namespace Biz\S2B2C\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\Dao\CourseDao;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
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
     * @param $localCourseSet
     * @param $product
     * 更新课程的计划列表
     */
    public function syncCourses($localCourseSet, $product)
    {
        // 获取供应商的原课程
        $courseSet = $this->getS2B2CFacadeService()->getSupplierPlatformApi()->getSupplierCourseSetProductDetail($product['remoteResourceId']);
        if (isset($courseSet['error'])) {
            $this->getS2B2CLogger()->error("[syncCourses]getCoursesDetailFail: {$courseSet['error']}");

            return;
        }
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
        if (empty($s2b2cConfig['supplierId']) || empty($courseSet['s2b2cDistributeId'])) {
            $this->createNewException(CommonException::ERROR_PARAMETER_MISSING());
        }

        $courses = $courseSet['courses'];
        $courseHasDefaultCourse = false;
        foreach ($courses as $course) {
            // 该计划没有分发，不同步
            if (!$course['s2b2cDistributeId']) {
                continue;
            }
            $course['courseSetId'] = $localCourseSet['id'];
            $course['courseSetTitle'] = $localCourseSet['title'];
            $course['platform'] = 'supplier';
            $localCourse = $this->getCourseService()->createCourse($course);
            $product = $this->getProductService()->createProduct([
                'supplierId' => $s2b2cConfig['supplierId'],
                'productType' => 'course',
                'remoteProductId' => $course['s2b2cDistributeId'],
                'remoteResourceId' => $course['id'],
                'localResourceId' => $localCourse['id'],
                'suggestionPrice' => $course['suggestionPrice'],
                'cooperationPrice' => $course['cooperationPrice'],
                'localVersion' => $course['editVersion'],
            ]);

            $this->syncCourseMain($localCourse['id']);

            if ($course['isDefault'] && $course['s2b2cDistributeId']) {
                // 如果供应商原课程中有默认计划，则直接同步
                $courseHasDefaultCourse = true;
                $this->getCourseSetService()->updateDefaultCourseId($localCourseSet['id'], $localCourse['id']);
            }
        }

        // 如果供应商原课程中没有默认计划，则按默认规则生成
        if (!$courseHasDefaultCourse) {
            $this->getCourseSetService()->updateCourseSetDefaultCourseId($localCourseSet['id']);
        }
    }

    /**
     * @param $courseId
     *
     * @return bool
     *              更新单个课程计划下的所有资源
     */
    public function syncCourseMain($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $product = $product = $this->getProductService()->getByTypeAndLocalResourceId('course', $course['id']);
        $courseSetProduct = $this->getProductService()->getByTypeAndLocalResourceId('course_set', $course['courseSetId']);
        $this->getLogger()->info("开始尝试同步课程(#{$courseId})");
        if (!$this->validateCourseData($course, $product)) {
            return false;
        }

        try {
            $this->beginTransaction();
            $sourceCourse = $this->tryGetSupplierProductSyncData($product['remoteProductId']);

            $this->biz['s2b2c.course_product_sync']->sync($sourceCourse, ['syncCourseId' => $courseId]);
            $syncCourse = $this->getProductService()->updateProduct($product['id'], ['localVersion' => $sourceCourse['editVersion'], 'syncStatus' => self::SYNC_STATUS_FINISHED]);
            $this->getProductService()->updateProduct($courseSetProduct['id'], ['syncStatus' => self::SYNC_STATUS_FINISHED]);
            $this->getLogService()->info('course', 'create', "内容市场选择商品(#{$courseId})《{$course['courseSetTitle']}》同步数据成功", ['userId' => $this->getCurrentUser()->getId()]);
            $this->commit();
            $this->getLogger()->info("[syncCourseProduct] 同步课程 - {$course['courseSetTitle']}(courseSetId#{$course['courseSetId']}) 成功", ['courseId' => $course['id']]);
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLogger()->error("[syncCourseProduct] 同步课程 - {$course['courseSetTitle']}(courseSetId#{$course['courseSetId']}) 失败", ['error' => $e]);
            $courseCounts = $this->getProductService()->countProducts(['remoteResourceId' => $course['courseSetId']]);
            //删除CourseSet存在一定风险，如果第一个计划就报错，会造成数据丢失
            if (1 == $courseCounts) {
                $this->getCourseSetService()->deleteCourseSet($course['courseSetId']);
                $this->getProductService()->deleteProduct($courseSetProduct['id']);
            }
            $this->getCourseService()->deleteCourse($courseId);
            $this->getProductService()->deleteProduct($product['id']);
            $this->getLogService()->error('course', 'create', "内容市场选择商品(#{$courseId})《{$course['courseSetTitle']}》同步数据失败", ['userId' => $this->getCurrentUser()->getId()]);

            return false;
        }

        return true;
    }

    public function updateCourseVersionData($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $product = $product = $this->getProductService()->getByTypeAndLocalResourceId('course', $course['id']);
        $courseSetProduct = $this->getProductService()->getByTypeAndLocalResourceId('course_set', $course['courseSetId']);
        $this->getLogger()->info("开始尝试更新课程(#{$courseId})到最新版本");
        if (empty($course)) {
            $this->getLogger()->error('课程不存在，拒绝操作');

            return ['status' => false, 'error' => '更新失败'];
        }
        if (self::SYNC_STATUS_FINISHED !== $product['syncStatus']) {
            $this->getLogger()->error("课程 - {$course['courseSetTitle']}(courseSetId#{$course['courseSetId']}) 状态异常，无法处理", ['syncStatus' => $product['syncStatus']]);

            return ['status' => false, 'error' => '更新失败'];
        }

        try {
            $this->beginTransaction();
            $sourceCourse = $this->tryGetSupplierProductSyncData($product['remoteProductId']);
            if ($product['localVersion'] >= $sourceCourse['editVersion']) {
                $this->getLogger()->info("课程 - {$course['courseSetTitle']}(courseSetId#{$course['courseSetId']}) 版本已经是最新，无需处理", ['nowVersion' => $product['localVersion'], 'sourceVersion' => $sourceCourse['editVersion']]);

                $this->commit();

                return ['status' => false, 'error' => '版本已经是最新，无需处理'];
            }
            $this->biz['s2b2c.course_product_sync']->updateToLastedVersion($sourceCourse, ['syncCourseId' => $courseId]);

            $syncCourse = $this->getProductService()->updateProduct($product['id'], ['localVersion' => $sourceCourse['editVersion'], 'changelog' => []]);
            //课程没有版本号，只有计划有，所以升级后版本号归1默认值
            $this->getProductService()->updateProduct($courseSetProduct['id'], ['localVersion' => 1, 'remoteVersion' => 1]);
            $this->getLogger()->info("[syncCourseProduct] 更新课程到最新 - {$course['courseSetTitle']}(courseSetId#{$course['courseSetId']}) 成功", ['courseId' => $course['id']]);
            $this->getLogService()->info('course', 'update', "(#{$courseId})《{$course['courseSetTitle']}》更新版本到最新成功,版本变动：V{$product['localVersion']}->V{$sourceCourse['editVersion']}", ['userId' => $this->getCurrentUser()->getId()]);
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLogger()->error("[syncCourseProduct] 更新课程到最新 - {$course['courseSetTitle']}(courseSetId#{$course['courseSetId']}) 失败", ['error' => $e]);
            $this->getLogService()->error('course', 'update', "(#{$courseId})《{$course['courseSetTitle']}》更新版本到最新失败，版本无变化：V{$product['localVersion']}", ['userId' => $this->getCurrentUser()->getId()]);
            $errorMsg = in_array($e->getCode(), [5001730, 5001731]) ? $e->getMessage() : '更新失败';

            return ['status' => false, 'error' => $errorMsg];
        }

        return ['status' => true, 'error' => ''];
    }

    public function setProductHasNewVersion($productType, $remoteResourceId)
    {
        //获取更新的课程和计划
        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
        $product = $this->getProductService()->getProductBySupplierIdAndRemoteResourceIdAndType($s2b2cConfig['supplierId'], $remoteResourceId, $productType);
        if (empty($product)) {
            $this->createNewException(S2B2CProductException::NOT_FOUND_PRODUCT());
        }
        $course = $this->getCourseService()->getCourse($product['localResourceId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $courseSetProduct = $this->getProductService()->getProductBySupplierIdAndLocalResourceIdAndType($s2b2cConfig['supplierId'], $courseSet['id'], 'course_set');
        if (empty($courseSetProduct) || self::SYNC_STATUS_FINISHED != $courseSetProduct['syncStatus']) {
            $this->createNewException(S2B2CProductException::NOT_FOUND_PRODUCT());
        }
        $this->getLogger()->info("[setProductHasNewVersion] 商品(#{$courseSet['id']}-{$courseSet['title']})存在新版本，进行更新字段", ['productId' => $product['remoteResourceId']]);

        //检验远程的数据是否有更新
        $productDetail = $this->getS2B2CFacadeService()->getSupplierPlatformApi()->getSupplierProductDetail($product['remoteProductId']);
        $productVersions = $this->getS2B2CFacadeService()->getSupplierPlatformApi()->getProductVersions($product['remoteResourceId']);
        if (!empty($productVersions['error']) || empty($productDetail['course']) || (!empty($productVersions) && $product['remoteResourceId'] != $productVersions[0]['productId'])) {
            return false;
        }

        //更新Changelog,标记有新版本生成
        $productVersions = $this->getProductService()->generateVersionChangeLogs($product['localVersion'], $productVersions);
        $hasNewVersion = $this->getCacheService()->get('s2b2c.hasNewVersion') ?: [];
        if (empty($hasNewVersion['courseSet'])) {
            $this->getCacheService()->set('s2b2c.hasNewVersion', array_merge($hasNewVersion, ['courseSet' => 1]));
        }
        //课程当前没有Version,所以本地版本+1
        $this->getProductService()->updateProduct($courseSetProduct['id'], [
            'remoteVersion' => $courseSetProduct['localVersion'] + 1,
        ]);

        return $this->getProductService()->updateProduct($product['id'], [
            'remoteVersion' => $productDetail['course']['editVersion'],
            'changelog' => $productVersions,
        ]);
    }

    /**
     * @param $products
     * 下架对应商品
     */
    public function closeProducts($products)
    {
        foreach ($products as $product) {
            if ('closed' != $product['syncStatus']) {
                $this->getLogger()->info("[closeSupplierCourseProduct] 开始关闭采购商品#{$product['id']}");
                $this->getProductService()->updateProduct($product['id'], ['syncStatus' => 'closed']);
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
     * @param $courseSet
     *
     * @return array|null[]|string[]
     */
    public function deleteProductsByCourseSet($courseSet)
    {
        if ('supplier' != $courseSet['platform']) {
            return ['error' => null];
        }

        $courses = $this->getCourseService()->findCoursesByCourseSetId($courseSet['id']);

        $courseSetProduct = $this->getProductService()->getByTypeAndLocalResourceId('course_set', $courseSet['id']);

        $courseProducts = $this->getProductService()->findProductsBySupplierIdAndProductTypeAndLocalResourceIds($courseSetProduct['supplierId'], 'course', ArrayToolkit::column($courses, 'id'));

        $productIds = ArrayToolkit::column($courseProducts, 'remoteResourceId');

        $result = $this->getS2B2CFacadeService()->getS2B2CService()->changePurchaseStatusToRemoved($courseSetProduct['remoteResourceId'], $productIds, 'course');

        $this->getProductService()->deleteProduct($courseSetProduct['id']);

        if (empty($courseProducts)) {
            return $result;
        }

        $this->getProductService()->deleteByIds(ArrayToolkit::column($courseProducts, 'id'));

        return $result;
    }

    protected function validateCourseData($course, $product)
    {
        if (empty($course)) {
            $this->getLogger()->error('课程不存在，拒绝操作');

            return false;
        }
        if ('waiting' !== $product['syncStatus']) {
            $this->getLogger()->info("课程 - {$course['courseSetTitle']}(courseSetId#{$course['courseSetId']}) 无需处理", ['syncStatus' => $product['syncStatus']]);

            return false;
        }

        return true;
    }

    protected function tryGetSupplierProductSyncData($distributeId)
    {
        $sourceCourse = $this->getS2B2CFacadeService()->getSupplierPlatformApi()->getSupplierProductSyncData($distributeId);

        if (empty($sourceCourse['id'])) {
            $this->getLogger()->info('[syncCourseProduct] 获取源平台课程数据失败', ['DATA' => $sourceCourse]);
            throw $this->createServiceException('获取新版本课程数据失败，请稍后重试', 5001730);
        }
        if ('published' != $sourceCourse['editStatus']) {
            $this->getLogger()->info('[syncCourseProduct] 源平台课程正在编辑中，无法处理', ['DATA' => $sourceCourse]);
            throw $this->createServiceException('源平台课程正在编辑中，无法更新，请稍后再试', 5001731);
        }

        return $sourceCourse;
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
}
