<?php

namespace Biz\Course\Job;

use Biz\Course\Service\CourseSetService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Dao\BatchUpdateHelper;

class UpdateCourseSetHotSeqJob extends AbstractJob
{
    public function execute()
    {
        $this->updateCourseHotSeq();
        $this->updateClassroomHotSeq();
    }

    protected function updateCourseHotSeq()
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

    protected function updateClassroomHotSeq()
    {
        $conditions = array('createdTime_GE' => strtotime('-30 days'), 'roles' => array('student', 'assistant'));
        $memberCount = $this->getClassroomService()->searchMemberCountGroupByFields($conditions, 'classroomId', 0, PHP_INT_MAX);

        $this->getClassroomService()->refreshClassroomHotSeq();

        if (!empty($memberCount)) {
            $batchHelper = new BatchUpdateHelper($this->getClassroomDao());

            foreach ($memberCount as $count) {
                $fields = array('hotSeq' => $count['count']);
                $batchHelper->add('id', $count['classroomId'], $fields);
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

    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }

    protected function getCourseSetDao()
    {
        return $this->biz->dao('Course:CourseSetDao');
    }

    protected function getClassroomDao()
    {
        return $this->biz->dao('Classroom:ClassroomDao');
    }
}
