<?php

namespace Biz\Activity\Service;

interface TestpaperActivityService
{
    public function getActivity($id);

    public function findActivitiesByIds($ids);

    public function findActivitiesByMediaIds($mediaIds);

    public function createActivity($fields);

    public function updateActivity($id, $fields);

    public function deleteActivity($id);
}
