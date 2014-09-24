<?php
namespace Topxia\Service\Classes;

interface SignService
{
    public function classMemberSign($userId, $classId);

    public function isSignedToday($userId, $classId);

    public function isYestodaySigned($userId, $classId);

    public function getSignRecordsByPeriod($userId, $classId, $startDay, $endDay);

    public function getClassMemberSignStatistics($userId, $classId);

    public function getClassSignStatistics($classId);
}