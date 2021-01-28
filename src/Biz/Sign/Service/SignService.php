<?php

namespace Biz\Sign\Service;

interface SignService
{
    public function userSign($userId, $targetType, $targetId);

    public function isSignedToday($userId, $targetType, $targetId);

    public function isYesterdaySigned($userId, $targetType, $targetId);

    public function findSignRecordsByPeriod($userId, $targetType, $targetId, $startDay, $endDay);

    public function getSignUserStatistics($userId, $targetType, $targetId);

    public function getSignTargetStatistics($targetType, $targetId, $date);

    public function getTodayRank($userId, $targetType, $targetId);
}
