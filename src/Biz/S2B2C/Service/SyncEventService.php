<?php

namespace Biz\S2B2C\Service;

interface SyncEventService
{
    const EVENT_MODIFY_PRICE = 'modifyPrice';

    const EVENT_CLOSE_TASK = 'closeTask';

    const EVENT_CLOSE_PLAN = 'closePlan';

    const EVENT_CLOSE_COURSE = 'closeCourse';

    public function searchSyncEvent($conditions, $orderBys, $start, $limit);

    /**
     * @param $productId
     * @param $events
     *
     * @return mixed
     */
    public function confirmByEvents($productId, $events);

    public function findNotifyByCourseSetIds($courseIds);
}
