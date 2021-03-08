<?php

namespace Biz\Classroom\Service\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\BaseService;
use Biz\Classroom\DateTimeRange;
use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\MemberService;
use Biz\Classroom\Service\ReportService;
use Biz\Common\CommonException;

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

    public function getStudentDetailList($classroomId, $filterConditions, $sort, $start, $limit)
    {
        $this->getClassroomService()->tryManageClassroom($classroomId);
        $conditions = $this->prepareStudentDetailFilterConditions($filterConditions);
        $orderBy = $this->prepareStudentDetailSort($sort);
        $this->getClassroomService()->searchMembers($conditions, $orderBy, $start, $limit);
    }

    /**
     * @param $filterConditions array
     * [
     *      'filter' => 'all',//all: 全部，unFinished: 未完成,sevenDaysUnLearn: 七日未学
     *      'nicknameLike' => 'abc',
     * ]
     *
     * @return array
     */
    protected function prepareStudentDetailFilterConditions($filterConditions)
    {
        if (empty($filterConditions['nicknameLike'])) {
        }

        if (!empty($filterConditions['filter'])) {
        }

        return [];
    }

    /**
     * @param $sort String
     * joinTimeDesc: 加入时间倒序，joinTimeAsc: 加入时间正序，learnedCompulsoryTaskNumDesc: 完成率倒序，learnedCompulsoryTaskNumAsc: 完成率正序
     *
     * @return array
     *
     * @throws \Exception
     */
    protected function prepareStudentDetailSort($sort)
    {
        $orderBy = [];
        switch ($sort) {
            case 'joinTimeDesc':
                $orderBy = ['createdTime' => 'DESC'];
                break;
            case 'joinTimeAsc':
                $orderBy = ['createdTime' => 'ASC'];
                break;
            case 'CompletionRateDesc':
                $orderBy = ['learnedCompulsoryTaskNum' => 'DESC'];
                break;
            case 'CompletionRateAsc':
                $orderBy = ['learnedCompulsoryTaskNum' => 'ASC'];
                break;
            default:
                $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        return $orderBy;
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
