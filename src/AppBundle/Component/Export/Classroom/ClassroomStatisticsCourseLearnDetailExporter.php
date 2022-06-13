<?php

namespace AppBundle\Component\Export\Classroom;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Component\Export\Exporter;
use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\ReportService;

class ClassroomStatisticsCourseLearnDetailExporter extends Exporter
{
    public function getTitles()
    {
        return [
            '用户名',
            '学习情况',
            '学习进度',
            '完成时间',
        ];
    }

    public function getContent($start, $limit)
    {
        $results = $this->getReportService()->getCourseLearnDetail($this->conditions['classroomId'], $this->conditions['courseId'], ['filter' => $this->conditions['filter']], $start, $limit);
        $userIds = ArrayToolkit::column($results, 'userId');

        $users = ArrayToolkit::index($this->getUserService()->findUsersByIds($userIds), 'id');
        $content = [];

        foreach ($results as $result) {
            $user = empty($users[$result['userId']]) ? [] : $users[$result['userId']];
            if ($result['isLearned']) {
                $statusText = '已完成';
            } elseif ($result['lastLearnTime'] > 0) {
                $statusText = '学习中';
            } else {
                $statusText = '未学';
            }
            $nickname = empty($user['nickname']) ? '--' : $user['nickname'];

            $content[] = [
                is_numeric($nickname) ? $nickname."\t" : $nickname,
                $statusText,
                $result['finishRate'].'%',
                $result['isLearned'] ? date('Y-m-d H:i:s', $result['finishedTime']) : '--',
            ];
        }

        return $content;
    }

    public function canExport()
    {
        return $this->getClassroomService()->canHandleClassroom($this->conditions['classroomId']);
    }

    public function getCount()
    {
        return $this->getReportService()->getCourseLearnDetailCount($this->conditions['classroomId'], $this->conditions['courseId'], ['filter' => 'all']);
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
