<?php

namespace Biz\InformationCollect\Service;

interface EventService
{
    const TARGET_TYPE_COURSE = 'course';
    const TARGET_TYPE_CLASSROOM = 'classroom';

    public function createEventWithLocations(array $fields);

    public function updateEventWithLocations($id, $updateFields);

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
