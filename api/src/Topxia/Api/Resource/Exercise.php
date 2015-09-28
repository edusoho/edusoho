<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Exercise extends BaseResource
{
    public function get(Application $app, Request $request, $id)
    {
        $isHomeworkInstalled = $this->getAppService()->getAppByCode('Homework');
        if (empty($isHomeworkInstalled)) {
            return $this->error('500', '网校不支持作业练习功能!');
        }

        $exerciseService = $this->getExerciseService();
        $exercise = $exerciseService->getExercise($id);
        if (empty($exercise)) {
            return $this->error('404', '该练习不存在!');
        }
        $itemSet = $exerciseService->getItemSetByExerciseId($id);

        return array_merge($exercise, $itemSet);
    }

    public function getByLesson(Application $app, Request $request, $id)
    {
        $isHomeworkInstalled = $this->getAppService()->getAppByCode('Homework');
        if (empty($isHomeworkInstalled)) {
            return $this->error('500', '网校不支持作业练习功能!');
        }
        $exerciseService = $this->getExerciseService();
        $exercise = $exerciseService->getExerciseByLessonId($id);
        if (empty($exercise)) {
            return $this->error('404', '该课时不存在练习!');
        }

        $itemSet = $exerciseService->getItemSetByExerciseId($exercise['id']);

        return array_merge($exercise, $itemSet);
    }

    public function post(Application $app, Request $request, $id)
    {
    }

    public function filter(&$res)
    {
        return $res;
    }

    protected function getExerciseService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.ExerciseService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }
}
