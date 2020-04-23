<?php

namespace Biz\S2B2C\Service\Impl;

use Biz\BaseService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Monolog\Logger;

/**
 * Class CourseProductServiceImpl
 */
class CourseProductServiceImpl extends BaseService implements CourseProductService
{
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
     * @return Logger
     */
    protected function getS2B2CLogger()
    {
        return $this->biz->offsetGet('s2b2c.merchant.logger');
    }
}
