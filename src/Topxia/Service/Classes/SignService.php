<?php
namespace Topxia\Service\Classes;

/**
 * @todo refactor: 去除第一个参数$courseId
 */
interface SignService
{
    public function classMemberSign($userId, $classId);

    public function isSignedToday($userId, $classId);

    public function getSignsRecordsByMonth($userId, $classId, $startDay, $endDay);

    public function getClassMemberSignStatistics($userId, $classId);

    public function getClassSignStatistics($classId);
}