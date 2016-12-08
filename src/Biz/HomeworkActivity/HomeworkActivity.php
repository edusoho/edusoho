<?php

namespace Biz\HomeworkActivity;

use Topxia\Common\ArrayToolkit;
use Biz\Activity\Config\Activity;
use Topxia\Common\Exception\InvalidArgumentException;
use Topxia\Common\Exception\ResourceNotFoundException;

class HomeworkActivity extends Activity
{
    public function getMetas()
    {
        return array(
            'name' => '做作业题',
            'icon' => 'es-icon es-icon-exam'
        );
    }

    protected function registerListeners()
    {
        return array(
            'homework.finish' => 'Biz\\HomeworkActivity\\Listener\\HomeworkFinishListener'
        );
    }

    public function registerActions()
    {
        return array(
            'create' => 'WebBundle:HomeworkActivity:create',
            'edit'   => 'WebBundle:HomeworkActivity:edit',
            'show'   => 'WebBundle:HomeworkActivity:show'
        );
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
            throw new ResourceNotFoundException('HomeworkActivity', $targetId);
        }

        $fields = $this->filterFields($fields);

        return $this->getTestpaperService()->updateTestpaper($homework['id'], $fields);
    }

    public function delete($targetId)
    {
        return $this->getTestpaperService()->deleteTestpaper($targetId);
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

        $fields['courseId'] = empty($fields['fromCourseSetId']) ? 0 : $fields['fromCourseSetId'];
        $fields['lessonId'] = 0;
        $fields['name']     = empty($fields['title']) ? '' : $fields['title'];

        return $fields;
    }

    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }
}
