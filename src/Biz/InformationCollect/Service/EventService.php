<?php

namespace Biz\InformationCollect\Service;

interface EventService
{
    public function createEventWithLocations(array $fields);

    public function count($conditions);

    public function search($conditions, $orderBy, $start, $limit);

    public function getEventByActionAndLocation($action, array $location);

    public function findItemsByEventId($eventId);

    public function closeCollection($id);

    public function openCollection($id);

    public function get($id);

    public function getEventLocations($id);

    public function searchLocations(array $conditions, array $orderBys, $start = 0, $limit = 20, $columns = []);

    public function countLocations(array $conditions);
}
