<?php

namespace Biz\S2B2C\Service\Impl;

use ApiBundle\Api\Resource\SyncProductNotify\NotifyEvent;
use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\S2B2C\Dao\SyncEventDao;
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

    /**
     * @param $params
     *
     * @return bool[]
     *                业务逻辑： 当网校不合作的时候，需要关闭所有的课程，是合作状态的时候，关闭某个课程
     * @Todo ？职责不单一奇怪的逻辑，S端必须改造这个逻辑！
     */
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
        if ('cooperatio1n' != $merchant['coop_status']) {
            $this->getLogger()->info("[refreshProductsStatus] 渠道商合作状态为:{$merchant['coop_status']}，非'cooperation',将对所有S课程下架处理");
            $courseProducts = $this->getProductService()->findProductsBySupplierIdAndProductType($params['supplierId'], 'course');
            $courseSetProducts = $this->getProductService()->findProductsBySupplierIdAndProductType($params['supplierId'], 'course_set');
            $this->getCourseProductService()->closeProducts($courseProducts);
            $this->getCourseProductService()->closeProducts($courseSetProducts);

            return ['status' => true];
        }
        //=====================分割线 上面是针对非合作模式的处理，需要关闭所有的课程；否则只关闭单个课程 分割线=================
        if (!ArrayToolkit::requireds($params, ['productId', 'productType'])) {
            throw CommonException::ERROR_PARAMETER_MISSING();
        }
        if ('course' != $params['productType']) {
            $this->getLogger()->info('[refreshProductsStatus] 暂不处理非课程的商品类型');

            return ['status' => true];
        }
        //既要关闭课程商品还要关闭计划商品。
        $courseProduct = $this->getProductService()->getProductBySupplierIdAndRemoteResourceIdAndType($params['supplierId'], $params['productId'], $params['productType']);
        if (!empty($courseProduct)) {
            $this->getCourseProductService()->closeProducts([$courseProduct]);
            $course = $this->getCourseService()->getCourse($courseProduct['localResourceId']);
            $courseSetProduct = $this->getProductService()->getProductBySupplierIdAndLocalResourceIdAndType($params['supplierId'], $course['courseSetId'], 'course_set');
            $this->getCourseProductService()->closeProducts([$courseSetProduct]);
            $this->getLogger()->info('[refreshProductsStatus] 处理完毕');
        }
        $this->getLogger()->info("[refreshProductsStatus] 对应商品不存在{$params['productId']}");

        return ['status' => true];
    }

    public function supplierCourseClosed($params)
    {
        $courseProduct = $this->getProductService()->getProductBySupplierIdAndRemoteResourceIdAndType($params['supplier_id'], $params['course_id'], 'course');
        if (empty($courseProduct)) {
            $this->getLogger()->info('[supplierCloseCourse] course not found in Merchant', $params);

            return ['status' => false];
        }

        try {
            $this->getCourseService()->closeCourse($courseProduct['localResourceId']);
            $this->getLogger()->info('[supplierCloseCourse] success');
        } catch (\Exception $e) {
            $this->getLogger()->err('[supplierCloseCourse] failed'.$e->getMessage().$e->getTraceAsString(), $params);

            return ['status' => false];
        }

        return ['status' => true];
    }

    public function supplierCourseSetClosed($params)
    {
        $courseSetProduct = $this->getProductService()->getProductBySupplierIdAndRemoteResourceIdAndType($params['supplier_id'], $params['course_set_id'], 'course_set');
        if (empty($courseSetProduct)) {
            $this->getLogger()->info('[supplierCourseSetClosed] course_set not found in Merchant', $params);

            return ['status' => false];
        }

        try {
            $this->getCourseSetService()->closeCourseSet($courseSetProduct['localResourceId']);
            $this->getLogger()->info('[supplierCourseSetClosed] success');
        } catch (\Exception $e) {
            $this->getLogger()->err('[supplierCourseSetClosed] failed'.$e->getMessage().$e->getTraceAsString(), $params);

            return ['status' => false];
        }

        return ['status' => true];
    }

    public function syncSupplierProductEvent($notifyEvent)
    {
        $this->getLogger()->info('[syncSupplierProductEvent] 同步supplier端信息', $notifyEvent->getData());
        $handle = [
            'modifyPrice' => 'modifyPriceEvent',
        ];

        if (!array_key_exists($notifyEvent->getEvent(), $handle)) {
            return false;
        }

        return $this->{$handle[$notifyEvent->getEvent()]}($notifyEvent);
    }

    /**
     * @param $notifyEvent
     *
     * @return bool
     */
    protected function modifyPriceEvent(NotifyEvent $notifyEvent)
    {
        $changeData = $notifyEvent->getData();

        $this->getCourseProductService()->syncProductPrice($notifyEvent->getProductId(), ArrayToolkit::parts($changeData['new'], ['suggestionPrice', 'cooperationPrice']));

        $this->getSyncEventDao()->create([
            'productId' => $notifyEvent->getProductId(),
            'event' => $notifyEvent->getEvent(),
            'data' => $notifyEvent->getData(),
        ]);

        return true;
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

    /**
     * @return SyncEventDao
     */
    protected function getSyncEventDao()
    {
        return $this->biz->dao('S2B2C:SyncEventDao');
    }
}
