<?php
namespace Topxia\Service\Course\Impl;

use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\BaseService;
use Topxia\Service\Course\ReportService;

class ReportServiceImpl extends BaseService implements ReportService
{
    public function summary($courseId)
    {
        $summary = array(
            'studentNum' => 0,
            'noteNum' => 0,
            'askNum' => 0,
            'discussionNum' => 0,
            'finishedNum' => 0,//完成人数
        );

        $course = $this->getCourseService()->getCourse($courseId);
        $summary['studentNum'] = $course['studentNum'];
        $summary['noteNum'] = $course['noteNum'];
        $summary['askNum'] = $this->getThreadService()->searchThreadCount(array('courseId' => $courseId, 'type' => 'question'));
        $summary['discussionNum'] = $this->getThreadService()->searchThreadCount(array('courseId' => $courseId, 'type' => 'discussion'));
        $summary['finishedNum'] = $this->getCourseService()->searchMemberCount(array('courseId' => $courseId, 'isLearned' => 1));

        return $summary;
    }


    protected function getCourseService()
    {
        return $this->createService('Course.CourseService');
    }

    protected function getThreadService()
    {
        return $this->createService('Course.ThreadService');
    }
}
