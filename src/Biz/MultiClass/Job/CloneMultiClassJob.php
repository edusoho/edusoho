<?php

namespace Biz\MultiClass\Job;

use Biz\Assistant\Service\AssistantStudentService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\System\Service\LogService;
use Biz\User\Service\NotificationService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CloneMultiClassJob extends AbstractJob
{
    public function execute()
    {
        $userId = $this->args['userId'];
        $multiClassId = $this->args['multiClassId'];
        $cloneMultiClass = $this->args['cloneMultiClass'];
        $multiClass = $this->getMultiClassService()->getMultiClass($multiClassId);
        $message = [
            'newTitle' => $cloneMultiClass['title'],
            'originTitle' => $multiClass['title'],
        ];

        try {
            $this->biz['db']->beginTransaction();
            $newMultiClass = $this->getMultiClassService()->cloneMultiClass($multiClassId, $cloneMultiClass);
            $course = $this->getCourseService()->getCourse($newMultiClass['courseId']);
            $this->getCourseSetService()->cloneCourseSet($course['courseSetId'], [
                'title' => $cloneMultiClass['courseSetTitle'],
                'newMultiClass' => $newMultiClass,
            ]);
            $newMultiClass = $this->getMultiClassService()->getMultiClass($newMultiClass['id']);
            $this->getCourseMemberService()->setCourseTeachers($newMultiClass['courseId'], [[
                'id' => $cloneMultiClass['teacherId'],
                'isVisable' => 1, ]], $newMultiClass['id']);
            $this->getCourseMemberService()->setCourseAssistants($newMultiClass['courseId'], $cloneMultiClass['assistantIds'], $newMultiClass['id']);
            $this->getAssistantStudentService()->setAssistantStudents($newMultiClass['courseId'], $newMultiClass['id']);

            $course = $this->getCourseService()->getCourse($newMultiClass['courseId']);
            $this->getCourseSetService()->publishCourseSet($course['courseSetId']);

            $message['status'] = 'success';
            $this->getNotificationService()->notify($userId, 'multi-class-copy', $message);

            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            $this->getLogService()->error('multi_class', 'multi_class_clone', "复制班课{$multiClassId}失败", $e->getMessage());
            $message['status'] = 'failure';
            $this->getNotificationService()->notify($userId, 'multi-class-copy', $message);
        }
    }

    /**
     * @return LogService
     */
    protected function getLogService()
    {
        return $this->biz->service('System:LogService');
    }

    /**
     * @return MultiClassService
     */
    protected function getMultiClassService()
    {
        return $this->biz->service('MultiClass:MultiClassService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->biz->service('Course:MemberService');
    }

    /**
     * @return AssistantStudentService
     */
    protected function getAssistantStudentService()
    {
        return $this->biz->service('Assistant:AssistantStudentService');
    }

    /**
     * @return NotificationService
     */
    private function getNotificationService()
    {
        return $this->biz->service('User:NotificationService');
    }
}
