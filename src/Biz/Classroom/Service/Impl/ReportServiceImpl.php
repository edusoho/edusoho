<?php

namespace Biz\Classroom\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Classroom\DateTimeRange;
use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\MemberService;
use Biz\Classroom\Service\ReportService;

class ReportServiceImpl extends BaseService implements ReportService
{
    public function getStudentTrend($classroomId, DateTimeRange $timeRange)
    {
        $studentsIncreaseData = ArrayToolkit::index($this->getClassroomMemberService()->findDailyIncreaseDataByClassroomIdAndRoleWithTimeRange(
            $classroomId,
            'student',
            $timeRange->getStartTime(),
            $timeRange->getEndDateTime()->modify('+1 day')->getTimestamp()
        ), 'date');

        $auditorsIncreaseData = ArrayToolkit::index($this->getClassroomMemberService()->findDailyIncreaseDataByClassroomIdAndRoleWithTimeRange(
            $classroomId,
            'auditor',
            $timeRange->getStartTime(),
            $timeRange->getEndDateTime()->modify('+1 day')->getTimestamp()
        ), 'date');

        $period = new \DatePeriod(
            $timeRange->getStartDateTime(),
            new \DateInterval('P1D'),
            $timeRange->getEndDateTime()->modify('+1 day')
        );

        $results = [];
        foreach ($period as $date) {
            $dateStr = $date->format('Y-m-d');
            $studentIncreaseNum = isset($studentsIncreaseData[$dateStr]) ? $studentsIncreaseData[$dateStr]['count'] : 0;
            $auditorIncreaseNum = isset($auditorsIncreaseData[$dateStr]) ? $auditorsIncreaseData[$dateStr]['count'] : 0;
            $results[] = [
                'date' => $dateStr,
                'studentIncrease' => $studentIncreaseNum,
                'auditorIncrease' => $auditorIncreaseNum,
            ];
        }

        return $results;
    }

    public function getCompletionRateTrend($classroomId, DateTimeRange $timeRange)
    {
    }

    public function getStudentDetail($classroomId, DateTimeRange $timeRange)
    {
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    /**
     * @return MemberService
     */
    protected function getClassroomMemberService()
    {
        return $this->biz->service('Classroom:MemberService');
    }
}
