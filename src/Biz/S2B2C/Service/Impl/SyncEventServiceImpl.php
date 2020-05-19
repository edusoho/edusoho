<?php

namespace Biz\S2B2C\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Course\Service\CourseService;
use Biz\S2B2C\Dao\SyncEventDao;
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

        $this->getSyncEventDao()->update(['ids' => ArrayToolkit::column($syncEvents, 'id')], ['isConfirm' => 1]);

        return ArrayToolkit::index($syncEvents, 'event');
    }

    public function findNotifyByCourseSetIds($courseSetIds)
    {
        $courses = $this->getCourseService()->findCoursesByCourseSetIds($courseSetIds);
        $productIds = ArrayToolkit::column($courses, 'sourceCourseId');

        $notifies = $this->searchSyncEvent(['productIds' => $productIds, 'events' => [SyncEventService::EVENT_MODIFY_PRICE, 'isConfirm' => 0]], ['createdTime' => 'asc'], 0, PHP_INT_MAX);
        $notifyProductIds = ArrayToolkit::column($notifies, 'productId');

        $courses = array_filter($courses, function ($course) use ($notifyProductIds) {
            return in_array($course['sourceCourseId'], $notifyProductIds);
        });

        return ArrayToolkit::index($courses, 'courseSetId');
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
}
