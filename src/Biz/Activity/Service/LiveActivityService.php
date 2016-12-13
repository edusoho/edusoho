<?php

namespace Biz\Activity\Service;


interface LiveActivityService
{
    public function getLiveActivity($id);

    public function createLiveActivity($activity);

    public function updateLiveActivity($id, $fields);

    public function deleteLiveActivity($id);
}
