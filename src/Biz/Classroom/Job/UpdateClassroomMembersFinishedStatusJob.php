<?php

namespace Biz\Classroom\Job;

use Biz\Classroom\Service\ClassroomService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;
use Codeages\Biz\Framework\Scheduler\Service\SchedulerService;

class UpdateClassroomMembersFinishedStatusJob extends AbstractJob
{
    const LIMIT = 500;

    public function execute()
    {
        $classroomId = $this->args['classroomId'];
        $start = empty($this->args['start']) ? 0 : $this->args['start'];
        $classroomMemberCount = $this->getClassroomService()->getClassroomStudentCount($classroomId);
        if (empty($classroomMemberCount)) {
            return;
        }
        $this->getClassroomService()->updateClassroomMembersFinishedStatusByLimit($classroomId, $start, self::LIMIT);
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
        return $this->biz->service('Scheduler:SchedulerService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->biz->service('Classroom:ClassroomService');
    }
}
