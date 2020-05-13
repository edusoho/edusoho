<?php

namespace Biz\S2B2C\Service\Impl;

use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\Dao\CourseSetDao;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\LogService;
use Biz\System\Service\SettingService;
use Monolog\Logger;

/**
 * Class CourseProductServiceImpl
 */
class CourseProductServiceImpl extends BaseService implements CourseProductService
{
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
        $this->getLogger()->info("开始尝试同步课程(#{$courseId})");
        if (!$this->validateCourseData($course, $product)) {
            return false;
        }

        try {
            $this->beginTransaction();
            $sourceCourse = $this->tryGetSupplierProductSyncData($product['remoteProductId']);

            $this->biz['s2b2c.course_product_sync']->sync($sourceCourse, ['syncCourseId' => $courseId]);
//
//            $syncCourse = $this->getCourseProductDao()->update($courseId, array('statusSyncTime' => time(), 'syncStatus' => self::SYNC_STATUS_FINISHED));
//            $this->getCourseSetProductDao()->update($syncCourse['courseSetId'], array('sourceVersion' => $syncCourse['sourceVersion'], 'syncStatus' => self::SYNC_STATUS_FINISHED, 'minCoursePrice' => $syncCourse['price'], 'maxCoursePrice' => $syncCourse['price']));
            $this->getLogService()->info('course', 'create', "内容市场选择商品(#{$courseId})《{$course['courseSetTitle']}》同步数据成功", ['userId' => $this->getCurrentUser()->getId()]);
            $this->commit();
            $this->getLogger()->info("[syncCourseProduct] 同步课程 - {$course['courseSetTitle']}(courseSetId#{$course['courseSetId']}) 成功", ['courseId' => $course['id']]);
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLogger()->error("[syncCourseProduct] 同步课程 - {$course['courseSetTitle']}(courseSetId#{$course['courseSetId']}) 失败", ['error' => $e]);
            $courseCounts = $this->getProductService()->countProducts(['remoteResourceId' => $course['courseSetId']]);
            //删除CourseSet存在一定风险，如果第一个计划就报错，会造成数据丢失
            if (1 == $courseCounts) {
//                $this->getCourseSetService()->delete($course['courseSetId']);
            }
//            $this->getCourseProductDao()->delete($courseId);
            $this->getLogService()->error('course', 'create', "内容市场选择商品(#{$courseId})《{$course['courseSetTitle']}》同步数据失败", ['userId' => $this->getCurrentUser()->getId()]);

            return false;
        }

        return true;
    }

//    public function setProductHasNewVersion($)

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
}
