<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class ExerciseResult extends BaseResource
{
    public function post(Application $app, Request $request, $exerciseId)
    {
        $answers = $request->request->all();

        $answers = $this->answerFormat($answers);
        $answers['usedTime'] = 0;

        $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($exerciseId, 'exercise');
        if (!$exercise) {
            return $this->error('404', '该练习不存在!');
        }

        $canTakeCourse = $this->getCourseService()->canTakeCourse($exercise['courseId']);
        if (!$canTakeCourse) {
            return $this->error('500', '无权限访问!');
        }

        $conditions = array(
            'mediaId' => $exercise['id'],
            'mediaType' => 'exercise',
            'fromCourseId' => $exercise['courseId'],
        );
        $activities = $this->getActivityService()->search($conditions, null, 0, 1);

        if (!$activities) {
            return $this->error('404', '该练习任务不存在!');
        }
        $lessonId = $activities[0]['id'];

        $result = $this->getTestpaperService()->startTestpaper($exercise['id'], array('lessonId' => $lessonId, 'courseId' => $exercise['courseId']));

        $this->getTestpaperService()->finishTest($result['id'], $answers);

        return array(
            'id' => $result['id'],
        );
    }

    public function get(Application $app, Request $request, $lessonId)
    {
        $user = $this->getCurrentUser();

        $task = $this->getTaskService()->getTask($lessonId);
        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], 'exercise');

        if (empty($exercise)) {
            return $this->error('404', '该练习不存在!');
        }

        $result = $this->getTestpaperService()->getUserLatelyResultByTestId($user['userId'], $exercise['id'], $exercise['courseId'], $activity['id'], 'exercise');

        if (empty($result)) {
            return $this->error('404', '没有该练习的结果记录!');
        }

        return $result;
    }

    private function filterItem($items)
    {
        $questionIds = ArrayToolkit::column($items, 'questionId');
        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);

        $materialMap = array();
        $itemIndexMap = array();
        $newItems = array();
        foreach ($items as &$item) {
            unset($item['answer']);
            unset($item['userId']);

            $question = $questions[$item['questionId']];
            $item['questionType'] = $question['type'];
            $item['questionParentId'] = $question['parentId'];

            if ('material' == $item['questionType']) {
                $itemIndexMap[$item['questionId']] = $item['id'];
                $materialMap[$item['questionId']] = array();
            }

            if (0 != $item['questionParentId'] && isset($materialMap[$item['questionParentId']])) {
                $materialMap[$item['questionParentId']][] = $item;
                continue;
            }

            $newItems[$item['id']] = $item;
        }

        foreach ($materialMap as $id => $material) {
            $newItems[$itemIndexMap[$id]]['items'] = $material;
        }

        return array_values($newItems);
    }

    public function filter($res)
    {
        $res['usedTime'] = date('c', $res['usedTime']);
        $res['updatedTime'] = date('c', $res['updatedTime']);
        $res['createdTime'] = date('c', $res['createdTime']);

        return $res;
    }

    private function answerFormat($answers)
    {
        if (empty($answers['data'])) {
            return array();
        }

        $data = array();
        foreach ($answers['data'] as $questionId => $value) {
            $data[$questionId] = empty($value['answer']) ? '' : $value['answer'];
        }

        return array('data' => $data);
    }

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper:TestpaperService');
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question:QuestionService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task:TaskService');
    }

    protected function getActivityService()
    {
        return $this->getServiceKernel()->createService('Activity:ActivityService');
    }
}
