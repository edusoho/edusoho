<?php

namespace Biz\ExerciseActivity;

use Topxia\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Topxia\Common\Exception\ResourceNotFoundException;

class ExerciseActivity extends Activity
{
    public function getMetas()
    {
        return array(
            'name' => '做练习题',
            'icon' => 'es-icon es-icon-mylibrarybooks'
        );
    }

    protected function registerListeners()
    {
        return array(
            'exercise.finish' => 'Biz\\ExerciseActivity\\Listener\\ExerciseFinishListener'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:ExerciseActivity:create',
            'edit'   => 'WebBundle:ExerciseActivity:edit',
            'show'   => 'WebBundle:ExerciseActivity:show'
        );
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
            throw new ResourceNotFoundException('ExerciseActivity', $targetId);
        }

        $fields = $this->filterFields($fields);

        return $this->getTestpaperService()->updateTestpaper($exercise['id'], $fields);
    }

    public function delete($targetId)
    {
        return $this->getTestpaperService()->deleteTestpaper($targetId);
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
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }
}
