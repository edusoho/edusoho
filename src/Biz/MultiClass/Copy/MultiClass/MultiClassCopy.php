<?php

namespace Biz\MultiClass\Copy\MultiClass;

use Biz\AbstractCopy;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\MultiClass\Dao\MultiClassDao;

class MultiClassCopy extends AbstractCopy
{
    protected function getFields()
    {
        return [
            'courseId',
        ];
    }

    public function preCopy($source, $options)
    {
        // TODO: Implement preCopy() method.
    }

    public function doCopy($multiClass, $options)
    {
        $newMultiClass = $this->partsFields($multiClass);
        $newMultiClass['copyId'] = $multiClass['id'];
        $newMultiClass['title'] = $multiClass['title']."(复制{$options['number']})";
        $newMultiClass['productId'] = $options['productId'];
        $newMultiClass = $this->getMultiClassDao()->create($newMultiClass);

        $course = $this->getCourseService()->getCourse($multiClass['courseId']);
        $courseSet = $this->getCourseSetService()->getCourseSet($course['courseSetId']);
        $courseSet['title'] = $courseSet['title']."(复制{$options['number']})";

        return ['multiClassCourseSet' => $courseSet, 'newMultiClassId' => $newMultiClass['id']];
    }

    /**
     * @return MultiClassDao
     */
    private function getMultiClassDao()
    {
        return $this->biz->dao('MultiClass:MultiClassDao');
    }

    /**
     * @return CourseService
     */
    private function getCourseService()
    {
        return $this->biz->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->biz->service('Course:CourseSetService');
    }
}
