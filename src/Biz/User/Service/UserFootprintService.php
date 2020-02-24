<?php

namespace Biz\User\Service;

interface UserFootprintService
{
    public function createUserFootprint($footprint);

    public function updateFootprint($id, $footprint);

    public function searchUserFootprints(array $conditions, array $order, $start, $limit, $columns = array());

    public function countUserFootprints($conditions);

    public function prepareUserFootprintsByType($footprints, $type);

    public function deleteUserFootprintsBeforeDate($date);
}
