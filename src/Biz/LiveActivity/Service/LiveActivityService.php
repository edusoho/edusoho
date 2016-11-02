<?php

namespace Biz\LiveActivity\Service;

use Biz\LiveActivity\Config\LiveActivity;

interface LiveActivityService
{
    public function createLiveActivity($activity);

    public function updateLiveActivity($id, $fields);

    public function deleteLiveActivity($id);
}
