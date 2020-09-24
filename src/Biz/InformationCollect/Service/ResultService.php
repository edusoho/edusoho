<?php

namespace Biz\InformationCollect\Service;

interface ResultService
{
    public function countGroupByEventId($eventIds);

    public function isSubmited($userId, $eventId);

    public function searchCollectedData($conditions, $orderBy, $start, $limit);

    public function getItemsByResultIdAndEventId($resultId, $eventId);
}
