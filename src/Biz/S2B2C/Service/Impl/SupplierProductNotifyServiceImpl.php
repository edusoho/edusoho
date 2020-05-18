<?php

namespace Biz\S2B2C\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\S2B2C\Service\CourseProductService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\SupplierProductNotifyService;
use Monolog\Logger;
use QiQiuYun\SDK\Service\S2B2CService;

class SupplierProductNotifyServiceImpl extends BaseService implements SupplierProductNotifyService
{
    public function setProductHasNewVersion($params)
    {
        if (!ArrayToolkit::requireds($params, ['productId', 'productType'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $result = $this->getCourseProductService()->setProductHasNewVersion($params['productType'], $params['productId']);

        return ['status' => !empty($result['id'])];
    }

    public function refreshProductsStatus($params)
    {
        if (!ArrayToolkit::requireds($params, ['supplierId'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        $this->getLogger()->info('[refreshProductsStatus] 开始刷新S课程状态');
        $merchant = $this->getS2B2CServiceApi()->getMe();
        if (!isset($merchant['coop_status'])) {
            $this->getLogger()->error('[refreshProductsStatus] 获取渠道商信息失败，停止处理[DATA]：'.json_encode($merchant));

            return ['status' => true];
        }
        $this->getLogger()->info('[refreshProductsStatus] 获取渠道商信息成功');
        if ('cooperation' != $merchant['coop_status']) {
            $this->getLogger()->info("[refreshProductsStatus] 渠道商合作状态为:{$merchant['coop_status']}，非'cooperation',将对所有S课程下架处理");
            $supplierCourses = $this->getProductService()->findOriginPlatformCourses('supplier', $params['supplierId']);
            $this->closeCourses($supplierCourses);

            return ['status' => true];
        }
        if (!ArrayToolkit::requireds($params, ['productId', 'productType'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        if ('course' != $params['productType']) {
            $this->getLogger()->info('[refreshProductsStatus] 暂不处理非课程的商品类型');

            return ['status' => true];
        }
        $supplierCourses = $this->getProductService()->getOriginPlatformCourse('supplier', $params['supplierId'], $params['productId']);
        $this->closeCourses([$supplierCourses]);

        $this->getLogger()->info('[refreshProductsStatus] 处理完毕');

        return ['status' => true];
    }

    private function closeCourses($supplierCourses)
    {
        foreach ($supplierCourses as $course) {
            if ('published' != $course['status']) {
                $this->getLogger()->info("课程#:{$course['id']}，未发布，不进行处理");
                continue;
            }
            $this->getLogger()->info("开始下架#:{$course['id']}，课程");
            $this->getProductService()->closeSupplierCourseProduct($course['id']);
            $this->getLogger()->info("课程#:{$course['id']}，下架成功");
        }
    }

    public function supplierCourseClosed($params)
    {
        $course = $this->getProductService()->getOriginPlatformCourse('supplier', $params['supplier_id'], $params['course_id']);
        if (empty($course)) {
            $this->getLogger()->info('[supplierCloseCourse] course not found in Merchant', $params);

            return ['status' => false];
        }

        try {
            $this->getCourseService()->closeCourse($course['id']);
            $this->getLogger()->info('[supplierCloseCourse] success');
        } catch (\Exception $e) {
            $this->getLogger()->err('[supplierCloseCourse] failed'.$e->getMessage().$e->getTraceAsString(), $params);

            return ['status' => false];
        }

        return ['status' => true];
    }

    public function supplierCourseSetClosed($params)
    {
        $courseSet = $this->getProductService()->getOriginPlatformCourseSet('supplier', $params['supplier_id'], $params['course_set_id']);
        if (empty($courseSet)) {
            $this->getLogger()->info('[supplierCourseSetClosed] course_set not found in Merchant', $params);

            return ['status' => false];
        }

        try {
            $this->getCourseSetService()->closeCourseSet($courseSet['id']);
            $this->getLogger()->info('[supplierCourseSetClosed] success');
        } catch (\Exception $e) {
            $this->getLogger()->err('[supplierCourseSetClosed] failed'.$e->getMessage().$e->getTraceAsString(), $params);

            return ['status' => false];
        }

        return ['status' => true];
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }

    /**
     * @return CourseProductService
     */
    protected function getCourseProductService()
    {
        return $this->createService('S2B2C:CourseProductService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return S2B2CService
     */
    protected function getS2B2CServiceApi()
    {
        return $this->biz->offsetGet('qiQiuYunSdk.s2b2cService');
    }

    /**
     * @return Logger
     */
    protected function getLogger()
    {
        return $this->biz->offsetGet('s2b2c.merchant.logger');
    }
}
