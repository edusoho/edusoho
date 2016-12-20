<?php

namespace Biz\Activity\Type;

use Topxia\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;

class Exercise extends Activity
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

        return $this->getTestpaperService()->buildTestpaper($fields, 'exercise');
    }

    public function update($targetId, $fields)
    {
        $exercise = $this->get($targetId);

        if (!$exercise) {
            throw $this->createNotFoundException('教学活动不存在');
        }

        $fields = $this->filterFields($fields);

        return $this->getTestpaperService()->updateTestpaper($exercise['id'], $fields);
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
        $fields = ArrayToolkit::parts($fields, array(
            'title',
            'range',
            'itemCount',
            'difficulty',
            'questionTypes',
            'finishCondition',
            'fromCourseId',
            'fromCourseSetId'
        ));

        $fields['courseSetId'] = empty($fields['fromCourseSetId']) ? 0 : $fields['fromCourseSetId'];
        $fields['courseId']    = empty($fields['fromCourseId']) ? 0 : $fields['fromCourseId'];
        $fields['lessonId']    = 0;
        $fields['name']        = empty($fields['title']) ? '' : $fields['title'];

        return $fields;
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }
}
