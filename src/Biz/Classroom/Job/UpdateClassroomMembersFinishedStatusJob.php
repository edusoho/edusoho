<?php

namespace Biz\Classroom\Job;

use Biz\Activity\Service\ActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\MemberService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;
use Topxia\Service\Common\ServiceKernel;

class UpdateClassroomMembersFinishedStatusJob extends AbstractJob
{
    const LIMIT = 2000;

    public function execute()
    {
        $classroomId = $this->args['classroomId'];
        $start = empty($this->args['start']) ? 0 : $this->args['start'];
        $classroomMemberCount = $this->getClassroomService()->countMembersByClassroomId($classroomId, array());
        if (empty($classroomMemberCount)) {
            return;
        }
        $this->getClassroomService()->updateClassroomMembersFinishedStatusByLimit($classroomId, $start);
        if ($start + self::LIMIT > $classroomMemberCount) {
            return;
        }

        $start += self::LIMIT;
        $this->createdUpdateClassroomMembersFinishedStatusJob($classroomId, $start);
    }

    protected function createdUpdateClassroomMembersFinishedStatusJob($classroomId, $start)
    {
        $startJob = [
            'name' => 'UpdateClassroomMembersFinishedStatusJob'.'_'.$classroomId.'_'.$start,
            'expression' => time() - 100,
            'class' => 'Biz\Classroom\Job\UpdateClassroomMembersFinishedStatusJob',
            'misfire_threshold' => 10 * 60,
            'args' => [
                'classroomId' => $classroomId,
                'start' => $start,
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
