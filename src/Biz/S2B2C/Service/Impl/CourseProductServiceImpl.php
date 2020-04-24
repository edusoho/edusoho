<?php

namespace Biz\S2B2C\Service\Impl;

use Biz\BaseService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\System\Service\LogService;
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

        $courses = $courseSet['courses'];
        $courseHasDefaultCourse = false;
        foreach ($courses as $course) {
            // 该计划没有分发，不同步
            if (!$course['s2b2cDistributeId']) {
                continue;
            }
            $course['syncStatus'] = 'waiting';
            $course['courseSetId'] = $localCourseSet['id'];
            $course['courseSetTitle'] = $localCourseSet['title'];
            $localCourse = $this->getCourseService()->createCourse($course);
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
     * @return bool
     * 更新单个课程计划下的所有资源
     */
    public function syncCourseMain($courseId)
    {
        $course = $this->getCourseService()->getCourse($courseId);
        $this->getLogger()->info("开始尝试同步课程(#{$courseId})");
        if (!$this->validateCourseData($course)) {
            return false;
        }

        try {
            $this->beginTransaction();
            $sourceCourse = $this->tryGetSupplierProductSyncData($course['s2b2cDistributeId']);

            $this->biz['course_product_sync']->sync($sourceCourse, array('syncCourseId' => $courseId));
//
//            $syncCourse = $this->getCourseProductDao()->update($courseId, array('statusSyncTime' => time(), 'syncStatus' => self::SYNC_STATUS_FINISHED));
//            $this->getCourseSetProductDao()->update($syncCourse['courseSetId'], array('sourceVersion' => $syncCourse['sourceVersion'], 'syncStatus' => self::SYNC_STATUS_FINISHED, 'minCoursePrice' => $syncCourse['price'], 'maxCoursePrice' => $syncCourse['price']));
            $this->getLogger()->info("[syncCourseProduct] 同步课程 - {$course['courseSetTitle']}(courseSetId#{$course['courseSetId']}) 成功", array('courseId' => $course['id']));
            $this->getLogService()->info('course', 'create', "内容市场选择商品(#{$courseId})《{$course['courseSetTitle']}》同步数据成功", array('userId' => $this->getCurrentUser()->getId()));
            $this->commit();
        } catch (\Exception $e) {
            $this->rollback();
            $this->getLogger()->error("[syncCourseProduct] 同步课程 - {$course['courseSetTitle']}(courseSetId#{$course['courseSetId']}) 失败", array('error' => $e));
            $courseCounts = $this->getCourseProductDao()->count(array('courseSetId' => $course['courseSetId']));
            if (1 == $courseCounts) {
                $this->getCourseSetProductDao()->delete($course['courseSetId']);
            }
            $this->getCourseProductDao()->delete($courseId);
            $this->getLogService()->error('course', 'create', "内容市场选择商品(#{$courseId})《{$course['courseSetTitle']}》同步数据失败", array('userId' => $this->getCurrentUser()->getId()));

            return false;
        }

        return true;
    }

    protected function validateCourseData($course)
    {
        if (empty($course)) {
            $this->getLogger()->error('课程不存在，拒绝操作');

            return false;
        }
        if ('waiting' !== $course['syncStatus']) {
            $this->getLogger()->info("课程 - {$course['courseSetTitle']}(courseSetId#{$course['courseSetId']}) 无需处理", array('syncStatus' => $course['syncStatus']));

            return false;
        }

        return true;
    }

    protected function tryGetSupplierProductSyncData($distributeId)
    {
        $sourceCourse = $this->getS2B2CFacadeService()->getSupplierPlatformApi()->getSupplierProductSyncData($distributeId);

        if (empty($sourceCourse['id'])) {
            $this->getLogger()->info('[syncCourseProduct] 获取源平台课程数据失败', array('DATA' => $sourceCourse));
            throw $this->createServiceException('获取新版本课程数据失败，请稍后重试', 5001730);
        }
        if ('published' != $sourceCourse['editStatus']) {
            $this->getLogger()->info('[syncCourseProduct] 源平台课程正在编辑中，无法处理', array('DATA' => $sourceCourse));
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
}
