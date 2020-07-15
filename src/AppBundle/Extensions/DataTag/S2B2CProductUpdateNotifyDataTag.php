<?php

namespace AppBundle\Extensions\DataTag;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\S2B2C\Service\SyncEventService;

class S2B2CProductUpdateNotifyDataTag extends BaseDataTag implements DataTag
{
    public function getData(array $arguments)
    {
        $notify = $this->getSyncEventService()->searchSyncEvent(
            ['isConfirm' => 0, 'events' => ['modifyPrice', 'closeTask', 'closePlan', 'closeCourse']],
            ['createdTime' => 'desc'], 0, 5);

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

        foreach ($notifies as &$notify) {
            $product = $this->getProduct($notify);
            $localResource = $this->getLocalResource($product);
            $notify['eventName'] = $eventName[$notify['event']];
            $notify['title'] = 'course_set' == $product['productType'] ? $localResource['title'] : $localResource['courseSetTitle'];
            $notify = array_merge($notify, $this->getPath($notify, $localResource));
        }

        return $notifies;
    }

    protected function getProduct($notify)
    {
        if ('closeCourse' == $notify['event']) {
            $product = $this->getProductService()->getByProductIdAndRemoteResourceIdAndType($notify['productId'], $notify['data']['courseSetId'], 'course_set');
        } else {
            $product = $this->getProductService()->getByProductIdAndRemoteResourceIdAndType($notify['productId'], $notify['data']['courseId'], 'course');
        }

        return $product;
    }

    protected function getLocalResource($product)
    {
        if ('course_set' == $product['productType']) {
            return $this->getCourseSetService()->getCourseSet($product['localResourceId']);
        } else {
            return $this->getCourseService()->getCourse($product['localResourceId']);
        }
    }

    protected function getPath($notify, $localResource)
    {
        $path = [
            'modifyPrice' => 'course_set_manage_course_info',
            'closeTask' => 'course_set_manage_course_tasks',
            'closePlan' => 'course_set_manage_courses',
            'closeCourse' => 'course_set_manage_base',
        ];
        if ('closeCourse' == $notify['event']) {
            $pathParams = ['id' => $localResource['id']];
        } else {
            $pathParams = ['courseId' => $localResource['id'], 'courseSetId' => $localResource['courseSetId']];
        }

        return ['path' => $path[$notify['event']], 'pathParams' => $pathParams];
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

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }
}
