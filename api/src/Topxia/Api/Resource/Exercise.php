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
        if ($request->getMethod() != 'POST') {
            return $this->error('404', 'only allow post!');
        }
        $isHomeworkInstalled = $this->getAppService()->getAppByCode('Homework');
        if (empty($isHomeworkInstalled)) {
            return $this->error('500', '网校不支持作业练习功能!');
        }

        $exerciseService = $this->getExerciseService();
        $exercise = $exerciseService->getExercise($id);
        if (empty($exercise)) {
            return $this->error('404', '该练习不存在!');
        }

        $data = $request->request->all();
        $data = !empty($data['data']) ? $data['data'] : array();
        $result = $exerciseService->submitExercise($exercise['id'], $data);
        $course = $this->getCourseService()->getCourse($exercise['courseId']);
        $lesson = $this->getCourseService()->getCourseLesson($exercise['courseId'], $result['lessonId']);
        $exerciseService->finishExercise($course, $lesson, $exercise['courseId'], $id);

        return array('result' => 'success');
    }

    public function getResult(Application $app, Request $request, $id)
    {
        $user = $this->getCurrentUser();
        $isHomeworkInstalled = $this->getAppService()->getAppByCode('Homework');
        if (empty($isHomeworkInstalled)) {
            return $this->error('500', '网校不支持作业练习功能!');
        }
        $exerciseService = $this->getExerciseService();
        $exercise = $exerciseService->getExercise($id);
        if (empty($exercise)) {
            return $this->error('404', '该练习不存在!');
        }

        $itemSetResult = $exerciseService->getItemSetResultByExerciseIdAndUserId($exercise['id'], $user['id']);

        return array_merge($exercise, $itemSetResult);
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
