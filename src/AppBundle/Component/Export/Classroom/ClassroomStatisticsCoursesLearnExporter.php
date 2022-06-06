<?php

namespace AppBundle\Component\Export\Classroom;

use AppBundle\Component\Export\Exporter;
use Biz\Classroom\Service\ClassroomService;
use Biz\Classroom\Service\ReportService;

class ClassroomStatisticsCoursesLearnExporter extends Exporter
{
    public function getTitles()
    {
        return [
            '课程名称',
            '任务数',
            '已学完人数',
            '学习中人数',
            '未开始人数',
            '完课率',
        ];
    }

    public function getContent($start, $limit)
    {
        $results = $this->getReportService()->getCourseDetailList($this->conditions['classroomId'], $this->conditions, $start, $limit);

        $content = [];

        foreach ($results as $result) {
            $content[] = [
                $result['courseSetTitle'],
                $result['compulsoryTaskNum'],
                $result['finishedNum'],
                $result['learnNum'],
                $result['notStartedNum'],
                $result['rate'].'%',
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
        return $this->getReportService()->getCourseDetailCount($this->conditions['classroomId'], $this->conditions);
    }

    public function buildCondition($conditions)
    {
        if (!empty($conditions['titleLike'])) {
            $conditions['nameLike'] = $conditions['titleLike'];
        }

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
