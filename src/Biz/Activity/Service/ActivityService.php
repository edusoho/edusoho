<?php

namespace Biz\Activity\Service;

use Biz\Activity\Model\Activity;

interface ActivityService
{
    public function getActivity($id);

    public function createActivity($activity);

    public function updateActivity($id, $fields);

    public function deleteActivity($id);

    /**
     * @param string $type 活动类型
     * @return Activity
     */
    public function getActivityModel($type);

    public function trigger($activityId, $name, $data = array());

    public function getActivityTypes();
}
