<?php

namespace Biz\MultiClass\Job;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\MultiClass\Service\MultiClassService;
use Biz\System\Service\LogService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CloneMultiClassJob extends AbstractJob
{
    public function execute()
    {
        try {
            $this->biz['db']->beginTransaction();

            $multiClassId = $this->args['multiClassId'];
            $cloneMultiClass = $this->args['cloneMultiClass'];
            $newMultiClass = $this->getMultiClassService()->cloneMultiClass($multiClassId, $cloneMultiClass);
            $course = $this->getCourseService()->getCourse($newMultiClass['courseId']);
            $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
            $this->getCourseSetService()->cloneCourseSet($course['courseSetId'], [
                'title' => empty($cloneMultiClass['courseSetTitle']) ? $courseSet['title']."(复制{$newMultiClass['number']})" : $cloneMultiClass['courseSetTitle'],
                'newMultiClass' => $newMultiClass,
            ]);
            $multiClass = $this->getMultiClassService()->getMultiClass($newMultiClass['id']);
            $this->getCourseMemberService()->setCourseTeachers($multiClass['courseId'], [[
                'id' => $cloneMultiClass['teacherId'],
                'isVisable' => 1, ]], $multiClass['id']);
            $this->getCourseMemberService()->setCourseAssistants($multiClass['courseId'], $cloneMultiClass['assistantIds'], $multiClass['id']);

            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
            $this->getLogService()->error('multi_class', 'multi_class_clone', "复制班课{$multiClassId}失败", $e->getMessage());
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
}
