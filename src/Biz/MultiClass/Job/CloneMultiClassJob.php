<?php

namespace Biz\MultiClass\Job;

use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\MultiClass\Service\MultiClassService;
use Codeages\Biz\Framework\Scheduler\AbstractJob;

class CloneMultiClassJob extends AbstractJob
{
    public function execute()
    {
        try {
            $this->biz['db']->beginTransaction();

            $multiClassId = $this->args['multiClassId'];
            $newMultiClass = $this->getMultiClassService()->cloneMultiClass($multiClassId);
            $course = $this->getCourseService()->getCourse($newMultiClass['courseId']);
            $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
            $this->getCourseSetService()->cloneCourseSet($course['courseSetId'], [
                'title' => $courseSet['title']."(复制{$newMultiClass['number']})",
                'newMultiClass' => $newMultiClass,
            ]);

            $this->biz['db']->commit();
        } catch (\Exception $e) {
            $this->biz['db']->rollback();
        }
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
}
