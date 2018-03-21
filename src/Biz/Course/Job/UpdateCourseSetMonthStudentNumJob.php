<?php

namespace Biz\Course\Job;

use Biz\Course\Service\CourseSetService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class UpdateCourseSetMonthStudentNumJob extends AbstractJob
{
    public function execute()
    {
        $conditions = array('startTimeGreaterThan' => strtotime('-30 days'), 'classroomId' => 0, 'role' => 'student');
        $memberCount = $this->getCourseMemberService()->searchMemberCountGroupByFields($conditions, 'courseSetId', 0, PHP_INT_MAX);

        //把所有课程的monthStudentNum都更新为0
        $this->getCourseSetService()->refreshMonthStudentNum();

        if (!empty($memberCount)) {
            $batchHelper = new BatchUpdateHelper($this->getCourseSetDao());

            foreach ($memberCount as $count) {
                $fields = array('monthStudentNum' => $count['count']);
                $batchHelper->add('id', $count['courseSetId'], $fields);
            }

            $batchHelper->flush();
        }
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    protected function getCourseSetDao()
    {
        return $this->biz->dao('Course:CourseSetDao');
    }
}
