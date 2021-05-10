<?php

namespace Biz\InformationCollect\Service;

interface ResultService
{
    public function countGroupByEventId($eventIds);

    public function isSubmited($userId, $eventId);

    public function searchCollectedData($conditions, $orderBy, $start, $limit);

    public function findResultDataByResultIds($resultIds);

    public function getResultByUserIdAndEventId($userId, $eventId);

    public function findResultsByUserIdsAndEventId($userIds, $eventId);

    public function findResultItemsByResultId($resultId);

    public function submitForm($userId, $eventId, $form);

    public function count($conditions);
}
