<?php

namespace Biz\S2B2C\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Course\Service\CourseService;
use Biz\S2B2C\Dao\SyncEventDao;
use Biz\S2B2C\Service\ProductService;
use Biz\S2B2C\Service\S2B2CFacadeService;
use Biz\S2B2C\Service\SyncEventService;

class SyncEventServiceImpl extends BaseService implements SyncEventService
{
    public function searchSyncEvent($conditions, $orderBys, $start, $limit)
    {
        return $this->getSyncEventDao()->search($conditions, $orderBys, $start, $limit);
    }

    public function confirmByEvents($productId, $events)
    {
        $syncEvents = $this->searchSyncEvent([
            'productId' => $productId,
            'events' => $events,
            'isConfirm' => 0,
        ], ['createdTime' => 'asc'], 0, PHP_INT_MAX);

        if (empty($syncEvents)) {
            return true;
        }

        $this->getSyncEventDao()->update(['ids' => ArrayToolkit::column($syncEvents, 'id') ?: [0]], ['isConfirm' => 1]);

        return ArrayToolkit::index($syncEvents, 'event');
    }

    /**
     * @param $courseSetIds
     *
     * @return array
     *               Function From S2B2C：方法设计存在问题，返回内容和业务不匹配
     */
    public function findNotifyByCourseSetIds($courseSetIds)
    {
        $s2b2cConf = $this->getS2B2CFacadeService()->getS2B2CConfig();
        if (!$s2b2cConf['supplierId']) {
            return [];
        }
        $courses = ArrayToolkit::index($this->getCourseService()->findCoursesByCourseSetIds($courseSetIds), 'id');
        $products = ArrayToolkit::index($this->getProductService()->findProductsBySupplierIdAndProductTypeAndLocalResourceIds(
            $s2b2cConf['supplierId'],
            'course',
            ArrayToolkit::column($courses, 'id')
        ), 'remoteResourceId');

        $remoteResourceIds = ArrayToolkit::column($products, 'remoteResourceId');

        $notifies = $this->searchSyncEvent(['productIds' => $remoteResourceIds, 'events' => SyncEventService::EVENT_MODIFY_PRICE, 'isConfirm' => 0], ['createdTime' => 'asc'], 0, PHP_INT_MAX);
        foreach ($notifies as &$notify) {
            $course = empty($products[$notify['productId']]['localResourceId']) ? null : $courses[$products[$notify['productId']]['localResourceId']];
            $notify['courseId'] = empty($course) ? null : $course['id'];
            $notify['courseSetId'] = empty($course) ? null : $course['courseSetId'];
        }

        return $notifies;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return SyncEventDao
     */
    protected function getSyncEventDao()
    {
        return $this->biz->dao('S2B2C:SyncEventDao');
    }

    /**
     * @return ProductService
     */
    protected function getProductService()
    {
        return $this->createService('S2B2C:ProductService');
    }

    /**
     * @return S2B2CFacadeService
     */
    protected function getS2B2CFacadeService()
    {
        return $this->createService('S2B2C:S2B2CFacadeService');
    }
}
