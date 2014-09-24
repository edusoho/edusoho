<?php
namespace Topxia\Service\Classes;

interface SignService
{
    // sign($userId, $classId)
    public function classMemberSign($userId, $classId);

    public function isSignedToday($userId, $classId);

    public function isYestodaySigned($userId, $classId);

    // getUserSignRecordsByMonth($userId, $classId, $month);
    public function getSignsRecordsByMonth($userId, $classId, $startDay, $endDay);

    // getUserSignStatistics
    public function getClassMemberSignStatistics($userId, $classId);

    public function getClassSignStatistics($classId);
}