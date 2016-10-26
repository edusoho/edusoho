<?php

namespace Biz\LiveActivity\Service;

interface LiveActivityService
{
    public function getActivityDetail($id);

    public function createActivityDetail($activity);

    public function updateActivityDetail($id, $fields);

    public function deleteActivityDetail($id);
}
