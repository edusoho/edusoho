<?php

namespace AppBundle\Component\Export\Classroom;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;
use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\ReportService;

class ClassroomStatisticsStudentsLearnExporter extends Exporter
{
    public function getTitles()
    {
        return [
            '用户名',
            '加入班级时间',
            '课程累加学习时长（分）',
            '完课率',
        ];
    }

    public function getContent($start, $limit)
    {
        $membersResult = $this->getReportService()->getStudentDetailList($this->parameter['classroomId'], $this->conditions, $this->parameter['sort'], $start, $limit);
        $userIds = ArrayToolkit::column($membersResult, 'userId');

        $users = ArrayToolkit::index($this->getUserService()->findUsersByIds($userIds), 'id');
        $content = [];
        foreach ($membersResult as $memberResult) {
            $user = empty($users[$memberResult['userId']]) ? [] : $users[$memberResult['userId']];
            $content[] = [
                empty($user) ? '--' : $user['nickname'],
                date('Y-m-d H:i', $membersResult['createdTime']),
                empty($memberResult['learnedTime']) ? 0 : $memberResult['learnedTime'],
                $membersResult['rate'].'%',
            ];
        }

        return $content;
    }

    public function canExport()
    {
        if ($this->getClassroomService()->canManageClassroom($this->parameter['classroomId'])) {
            return true;
        }

        return false;
    }

    public function getCount()
    {
        return $this->getReportService()->getStudentDetailCount($this->parameter['classroomId'], $this->conditions);
    }

    public function buildCondition($conditions)
    {
        return $conditions;
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return ReportService
     */
    protected function getReportService()
    {
        return $this->getBiz()->service('Classroom:ReportService');
    }
}
