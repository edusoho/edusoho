<?php

namespace Biz\Activity\Type;

use Topxia\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Topxia\Common\Exception\InvalidArgumentException;

class Homework extends Activity
{
    protected function registerListeners()
    {
        return array();
    }

    public function get($targetId)
    {
        return $this->getTestpaperService()->getTestpaper($targetId);
    }

    public function create($fields)
    {
        $fields = $this->filterFields($fields);

        return $this->getTestpaperService()->buildTestpaper($fields, 'homework');
    }

    public function update($targetId, $fields)
    {
        $homework = $this->get($targetId);

        if (!$homework) {
            throw $this->createNotFoundException('教学活动不存在');
        }

        $fields = $this->filterFields($fields);

        return $this->getTestpaperService()->updateTestpaper($homework['id'], $fields);
    }

    public function delete($targetId)
    {
        return $this->getTestpaperService()->deleteTestpaper($targetId);
    }

    protected function getListeners()
    {
        return array();
    }

    protected function filterFields($fields)
    {
        if (!ArrayToolkit::requireds($fields, array(
            'finishCondition'
        ))
        ) {
            throw new InvalidArgumentException('homework fields is invalid');
        }

        $fields = ArrayToolkit::parts($fields, array(
            'title',
            'description',
            'questionIds',
            'finishCondition',
            'fromCourseId',
            'fromCourseSetId'
        ));

        $finishCondition = array();
        if (!empty($fields['finishCondition'])) {
            $finishCondition['type'] = $fields['finishCondition'];
        }

        $fields['finishCondition'] = $finishCondition;

        $fields['courseSetId'] = empty($fields['fromCourseSetId']) ? 0 : $fields['fromCourseSetId'];
        $fields['courseId']    = empty($fields['fromCourseId']) ? 0 : $fields['fromCourseId'];
        $fields['lessonId']    = 0;
        $fields['name']        = empty($fields['title']) ? '' : $fields['title'];

        return $fields;
    }

    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }
}
