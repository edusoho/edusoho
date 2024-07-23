<?php

namespace Biz\Course\Job;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Topxia\Service\Common\ServiceKernel;

class ClassroomCourseBatchBecomeStudentsJob extends AbstractJob
{
    const LIMIT = 2000;

    public function execute()
    {
        $classroomId = $this->args['classroomId'];
        $start = $this->args['start'];
        $courseId = $this->args['courseId'];
        if (!$this->getClassroomService()->isCourseInClassroom($courseId, $classroomId)) {
            return;
        }
        $classroomMemberCount = $this->getClassroomService()->countMembersByClassroomId($classroomId, []);
        if (empty($classroomMemberCount)) {
            return;
        }
        $members = $this->getClassroomService()->findClassroomStudents($classroomId, $start, self::LIMIT);
        if (empty($members)) {
            return;
        }
        $memberIds = ArrayToolkit::column($members, 'userId');
        $this->getCourseMemberService()->batchBecomeStudents($courseId, $memberIds, $classroomId);
        if ($start + self::LIMIT > $classroomMemberCount) {
            return;
        }
        $start += self::LIMIT;
        $this->createdClassroomCourseBatchBecomeStudentsJob($classroomId, $courseId, $start);
    }

    protected function createdClassroomCourseBatchBecomeStudentsJob($classroomId, $courseId, $start)
    {
        $startJob = [
            'name' => 'ClassroomCourseBatchBecomeStudentsJob'.'_'.$classroomId.'_'.$courseId.'_'.$start,
            'expression' => time() - 100,
            'class' => 'Biz\Course\Job\ClassroomCourseBatchBecomeStudentsJob',
            'misfire_threshold' => 10 * 60,
            'args' => [
                'classroomId' => $classroomId,
                'start' => $start,
                'courseId' => $courseId,
            ],
        ];
        $this->getSchedulerService()->register($startJob);
    }

    /**
     * @return SchedulerService
     */
    protected function getSchedulerService()
    {
        return ServiceKernel::instance()->createService('Scheduler:SchedulerService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return ServiceKernel::instance()->createService('Activity:ActivityService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return ServiceKernel::instance()->createService('Classroom:ClassroomService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return ServiceKernel::instance()->createService('Course:MemberService');
    }
}
