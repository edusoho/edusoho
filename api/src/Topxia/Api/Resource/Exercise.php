<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;

class Exercise extends BaseResource
{
     public function get(Application $app, Request $request, $id) 
     {
        $idType = $request->query->get('_idType');
        if ('lesson' == $idType) {
            $exercise = $this->getExerciseService()->getExerciseByLessonId($id);
        } else {
            $exercise = $this->getExerciseService()->getExercise($id);
        }

        if (empty($exercise)) {
            return $this->error('404', '该练习不存在!');
        }

        $course = $this->getCorrseService()->getCourse($exercise['courseId']);
        $exercise['courseTitle'] = $course['title'];
        $lesson = $this->getCorrseService()->getLesson($exercise['lessonId']);
        $exercise['lessonTitle'] = $lesson['title'];
        $exercise['description'] = $lesson['title'];

        $typeRange = $exercise['questionTypeRange'];
        $typeRange = $this->getquestionTypeRangeStr($typeRange);
        $excludeIds = $this->getRandQuestionIds($typeRange,$exercise['itemCount'],$exercise['source'],$course['id'],$exercise['lessonId']);

        $result = $this->getExerciseService()->startExercise($exercise['id'],$excludeIds);
        
        if (empty($exercise)) {
            $exercise = array();
            return $exercise;
        }

        if ('lesson' != $idType) {
            $result = $this->doStart($exercise);
            if (empty($result)) {
                return $this->error('404', '该练习不存在!'); 
            }
            $rawItems = $this->getExerciseService()->getItemSetByExerciseId($exercise['id']);
            $items = $rawItems['items'];
            $items = $this->filterQuestion($items);
            
            $indexdItems = ArrayToolkit::index($items, 'questionId');
            $questions = $this->getQuestionService()->findQuestionsByIds(array_keys($indexdItems));
            $exercise['items'] = $this->filterItem($questions, null);
        }
        
        return $this->filter($exercise);
    }

    public function result(Application $app, Request $request, $id)
    {
        $user = $this->getCurrentUser();
        $exerciseResult = $this->getExerciseService()->getItemSetResultByExerciseIdAndUserId($id,$user->id);
        $exercise = $this->getExerciseService()->getExercise($id);

        $course = $this->getCorrseService()->getCourse($exercise['courseId']);
        $exercise['courseTitle'] = $course['title'];
        $lesson = $this->getCorrseService()->getLesson($exercise['lessonId']);
        $exercise['lessonTitle'] = $lesson['title'];
        $exercise["description"] = $lesson['title'];

        $rawItems = $this->getExerciseService()->getItemSetByExerciseId($exercise['id']);
        $items = $rawItems['items'];
        $items = $this->filterQuestion($items);
        $indexdItems = ArrayToolkit::index($items, 'questionId');
        $questions = $this->getQuestionService()->findQuestionsByIds(array_keys($indexdItems));

        $rawItemSetResults = $this->getExerciseService()->getItemSetResultByExerciseIdAndUserId($id,$user->id);
        $itemSetResults = $rawItemSetResults['items'];
        $itemSetResults = $this->filterQuestion($itemSetResults);
        $itemSetResults = ArrayToolkit::index($itemSetResults, 'questionId');
        $exercise['items'] = $this->filterItem($questions, $itemSetResults);
        return $this->filterResult($exercise);
    }

    private function filterItem($items, $itemSetResults)
    {
        $newItmes = array();
        $materialMap = array();
        foreach ($items as $item) {
            $item = ArrayToolkit::parts($item, array('id', 'type', 'stem', 'answer', 'analysis', 'metas', 'difficulty', 'parentId'));
            if (empty($item['metas'])) {
                $item['metas'] = array();
            }
            if (isset($item['metas']['choices'])) {
                $metas = array_values($item['metas']['choices']);
                $item['metas'] = $metas;
            }

            $item['answer'] = $this->filterAnswer($item, $itemSetResults);

            if ('material' == $item['type']) {
                $materialMap[$item['id']] = array();
            }

            if ($itemSetResults) {
                $item['result'] = $itemSetResults[$item['id']];
            }
            
            $item['stem'] = $this->coverDescription($item['stem']);
            if ($item['parentId'] != 0 && isset($materialMap[$item['parentId']])) {
                $materialMap[$item['parentId']][] = $item;
                continue;
            }
            
            $item['items'] = array();
            $newItmes[$item['id']] = $item;
        }

        foreach ($materialMap as $id => $material) {
            $newItmes[$id]['items'] = $material;
        }

        return array_values($newItmes);
    }

    public function filterQuestion(&$res){
        foreach ($res as &$value) {
            $value['questionType']=$value['question']['type'];
            unset($value['question']);
            if (array_key_exists('subItems',$value)) {
                foreach ($value['subItems'] as &$subItem) {
                    $subItemId = $subItem['question']['id'];
                    $res[$subItemId] = $subItem;
                }
                unset($value['subItems']);
            }
        }
        return $res;
    }

     public function filter($res)
    {
        $res = ArrayToolkit::parts($res, array('id', 'courseId', 'lessonId', 'description', 'itemCount', 'items', 'courseTitle', 'lessonTitle'));
        return $res;
    }

    public function filterResult(&$res)
    {
        $res = ArrayToolkit::parts($res, array('id', 'courseId', 'lessonId', 'description', 'itemCount', 'items', 'courseTitle', 'lessonTitle'));
        $items = $res['items'];
        foreach ($items as &$item) {
            unset($item['result']['score']);
            unset($item['result']['missScore']);
            unset($item['result']['question']);
            $item['result']=$item['result']['itemResult'];
            if (!empty($item['items'])) {
                foreach ($item['items'] as &$item) {
                    $item['result']=$item['result']['itemResult'];
                }
            }
        }
        $res['items']=$items;
        return $res;
    }

     private function filterAnswer($item, $itemSetResults) {

        if (empty($itemSetResults)) {
            if ('fill' == $item['type']) {
                return array_map(function($answer) {
                    return "";
                }, $item['answer']);
            }
            return null;
        }

        return $this->coverAnswer($item['answer']);
    }

    private function coverAnswer($answer) {
            if (is_array($answer)) {
                $answer = array_map(function($answerValue){
                    if (is_array($answerValue)) {
                        return implode('|', $answerValue);
                    }
                    return $answerValue;
                }, $answer);
                return $answer;
            }

            return array();
        }

    private function coverDescription($stem)
    {
        $ext = $this;
        $stem = preg_replace_callback('/\[image\](.*?)\[\/image\]/i', function($matches) use ($ext) {
            $url = $ext->getFileUrl($matches[1]);
            return "<img src='{$url}' />";
        }, $stem);

        return $stem;
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

    private function doStart($exercise)
    {
        $typeRange = $exercise['questionTypeRange'];
        $typeRange = $this->getquestionTypeRangeStr($typeRange);
        $excludeIds = $this->getRandQuestionIds($typeRange, $exercise['itemCount'], $exercise['source'], $exercise['courseId'], $exercise['lessonId']);

        $result = $this->getExerciseService()->startExercise($exercise['id'], $excludeIds);

        return $result;
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

    protected function getCorrseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}
