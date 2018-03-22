<?php

namespace Biz\Course\Job;

use Biz\Course\Service\CourseSetService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class UpdateCourseSetHotSeqJob extends AbstractJob
{
    public function execute()
    {
        $conditions = array('startTimeGreaterThan' => strtotime('-30 days'), 'classroomId' => 0, 'role' => 'student');
        $memberCount = $this->getCourseMemberService()->searchMemberCountGroupByFields($conditions, 'courseSetId', 0, PHP_INT_MAX);

        //把所有课程的hotSeq都更新为0
        $this->getCourseSetService()->refreshHotSeq();

        if (!empty($memberCount)) {
            $batchHelper = new BatchUpdateHelper($this->getCourseSetDao());

            foreach ($memberCount as $count) {
                $fields = array('hotSeq' => $count['count']);
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
