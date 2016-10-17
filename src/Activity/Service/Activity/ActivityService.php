<?php

namespace Activity\Service\Activity;

interface ActivityService
{
    public function getActivity($id);

    public function getActivityDetail($id);

    public function createActivity($activity);

    public function updateActivity($id, $fields);

    public function deleteActivity($id);

    public function findActivitiesByCourseId($courseId);

    public function getActivityTypes();

    public function trigger($name, $data);
}
