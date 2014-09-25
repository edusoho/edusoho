<?php
namespace Topxia\Service\Sign;

interface SignService
{
    public function userSign($userId, $targetType, $targetId);

    public function isSignedToday($userId, $targetType, $targetId);

    public function isYestodaySigned($userId, $targetType, $targetId);

    public function getSignRecordsByPeriod($userId, $targetType, $targetId, $startDay, $endDay);

    public function getSignUserStatistics($userId, $targetType, $targetId);

    public function getSignTargetStatistics($targetType, $targetId, $date);

    public function getTodayRank($userId, $targetType, $targetId);
}