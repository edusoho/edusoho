<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

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

        if (empty($itemSet['items'])) {
            $itemSet = $this->doStart($exercise);
        }

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

    private function doStart($exercise)
    {
        $typeRange = $exercise['questionTypeRange'];
        $typeRange = $this->getquestionTypeRangeStr($typeRange);
        $excludeIds = $this->getRandQuestionIds($typeRange, $exercise['itemCount'], $exercise['source'], $exercise['courseId'], $exercise['lessonId']);

        $this->getExerciseService()->startExercise($exercise['id'], $excludeIds);

        return $this->getExerciseService()->getItemSetByExerciseId($exercise['id']);
    }

    private function getquestionTypeRangeStr(array $questionTypeRange)
    {
        $questionTypeRangeStr = "";
        foreach ($questionTypeRange as $key => $questionType) {
            $questionTypeRangeStr .= "'{$questionType}',";
        }

        return substr($questionTypeRangeStr, 0, -1);
    }

    private function getRandQuestionIds($typeRange, $itemCount, $questionSource, $courseId, $lessonId)
    {
        $questionsCount = $this->getQuestionService()->findQuestionsCountbyTypesAndSource($typeRange, $questionSource, $courseId, $lessonId);

        $questions = $this->getQuestionService()->findQuestionsByTypesAndSourceAndExcludeUnvalidatedMaterial($typeRange, 0, $questionsCount, $questionSource, $courseId, $lessonId);
        $questionIds = ArrayToolkit::column($questions, 'id');

        $excludeIds = array_rand($questionIds, $itemCount);
        if (!is_array($excludeIds)) {
            $excludeIds = array($excludeIds);
        }
        $excludeIdsArr = array();
        foreach ($excludeIds as $key => $excludeId) {
            array_push($excludeIdsArr, $questions[$excludeId]['id']);
        }

        return $excludeIdsArr;
    }

    protected function getExerciseService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.ExerciseService');
    }

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }
}
