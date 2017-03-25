<?php

namespace Topxia\Api\Resource;

use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class HomeworkResult extends BaseResource
{
    public function post(Application $app, Request $request, $homeworkId)
    {
        //answer结构是否一致
        $answers = $request->request->all();
        $answers['usedTime'] = 0;

        $homework = $this->getTestpaperService()->getTestpaper($homeworkId);

        $canTakeCourse = $this->getCourseService()->canTakeCourse($homework['courseId']);
        if (!$canTakeCourse) {
            return $this->error('500', '无权限访问!');
        }

        $conditions = array(
            'mediaId' => $homework['id'],
            'mediaType' => 'homework',
            'fromCourseId' => $homework['courseId'],
        );
        $activities = $this->getActivityService()->search($conditions, null, 0, 1);

        if (!$activities) {
            return $this->error('404', '该作业任务不存在!');
        }

        //homework里获取不到activityId,只能间接获取
        $lessonId = $activities[0]['id'];
        $result = $this->getTestpaperService()->startTestpaper($homework['id'], array('lessonId' => $lessonId, 'courseId' => $homework['courseId']));

        try {
            $this->getTestpaperService()->finishTest($result['id'], $answers);
        } catch (\Exception $e) {
            return $this->error('500', $e->getMessage());
        }

        return array(
            'id' => $result['id'],
        );
    }

    public function get(Application $app, Request $request, $lessonId)
    {
        $user = $this->getCurrentUser();
        $task = $this->getTaskService()->getTask($lessonId);

        if ($task['type'] != 'homework') {
            $conditions = array(
                'categoryId' => $task['categoryId'],
                'type' => 'homework',
                'mode' => 'homework',
            );
            $tasks = $this->getTaskService()->searchTasks($conditions, null, 0, 1);
            if (!$tasks) {
                return $this->error('404', '该作业不存在!');
            }

            $task = array_shift($tasks);
        }

        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $homework = $this->getTestpaperService()->getTestpaper($activity['mediaId']);
        if (empty($homework)) {
            return $this->error('404', '该作业不存在!');
        }

        $canTakeCourse = $this->getCourseService()->canTakeCourse($homework['courseId']);
        if (!$canTakeCourse) {
            return $this->error('500', '无权限访问!');
        }

        $conditions = array(
            'testId' => $homework['id'],
            'userId' => $user['id'],
        );
        $homeworkResults = $this->getTestpaperService()->searchTestpaperResults($conditions, array('id' => 'desc'), 0, 1);

        if (empty($homeworkResults)) {
            return $this->error('404', '该作业结果不存在!');
        }
        $homeworkResult = $homeworkResults[0];
        $canTakeCourse = $this->getTestpaperService()->canLookTestpaper($homeworkResult['id']);
        if (!$canTakeCourse) {
            return $this->error('500', '无权限访问!');
        }
        $itemSetResults = $this->getTestpaperService()->findItemResultsByResultId($homeworkResult['id']);
        $homeworkResult['items'] = $this->filterItem($itemSetResults);

        return $this->filter($homeworkResult);
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

            if ($item['questionParentId'] != 0 && isset($materialMap[$item['questionParentId']])) {
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
        $res['usedTime'] = $res['usedTime'];
        $res['updatedTime'] = date('c', $res['updatedTime']);
        $res['createdTime'] = date('c', $res['beginTime']);

        return $res;
    }

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper:TestpaperService');
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question:QuestionService');
    }

    protected function getTaskService()
    {
        return $this->getServiceKernel()->createService('Task:TaskService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course:CourseService');
    }

    protected function getActivityService()
    {
        return $this->getServiceKernel()->createService('Activity:ActivityService');
    }
}
