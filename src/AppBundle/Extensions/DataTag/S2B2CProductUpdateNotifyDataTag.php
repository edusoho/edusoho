<?php

namespace AppBundle\Extensions\DataTag;

use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\S2B2C\Service\SyncEventService;

class S2B2CProductUpdateNotifyDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $notify = $this->getSyncEventService()->searchSyncEvent(['isConfirm' => 0], ['createdTime' => 'asc'], 0, 5);

        return $this->covertProducts($notify);
    }

    protected function covertProducts($notifies)
    {
        $eventName = [
            'modifyPrice' => '价格已更新',
            'closeTask' => '有课时关闭',
            'closePlan' => '教学计划关闭',
            'closeCourse' => '课程关闭',
        ];

        $productIds = ArrayToolkit::column($notifies, 'productId');

        $s2b2cConfig = $this->getS2B2CFacadeService()->getS2B2CConfig();
        if (empty($s2b2cConfig)) {
            return [];
        }

        $products = $this->getProductService()->findProductsBySupplierIdAndRemoteResourceTypeAndIds($s2b2cConfig['supplierId'], 'course', $productIds);
        $products = ArrayToolkit::index($products, 'remoteResourceId');
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($products, 'localResourceId'));
        $courses = ArrayToolkit::index($courses, 'id');

        foreach ($notifies as &$notify) {
            $notify['eventName'] = $eventName[$notify['event']];
            $notify['product'] = isset($products[$notify['productId']]) ? $products[$notify['productId']] : null;
            $notify['course'] = !empty($notify['product']) && isset($courses[$notify['product']['localResourceId']]) ? $courses[$notify['product']['localResourceId']] : null;
        }

        return $notifies;
    }

    /**
     * @return SyncEventService
     */
    protected function getSyncEventService()
    {
        return $this->createService('S2B2C:SyncEventService');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }

    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }
}
