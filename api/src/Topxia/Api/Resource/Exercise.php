<?php

namespace Topxia\Api\Resource;

use Biz\Accessor\AccessorInterface;
use Biz\Course\Service\CourseService;
use Silex\Application;
use AppBundle\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class Exercise extends BaseResource
{
    public function get(Application $app, Request $request, $id)
    {
        $idType = $request->query->get('_idType');
        if ('lesson' == $idType) {
            $task = $this->getTaskService()->getTask($id);
            $course = $this->getCourseService()->getCourse($task['courseId']);

            if (CourseService::DEFAULT_COURSE_TYPE != $course['courseType']) {
                return $this->error('404', '该练习不存在!');
            }

            //只为兼容移动端学习引擎2.0以前的版本，之后需要修改
            $conditions = array(
                'categoryId' => $task['categoryId'],
                'status' => 'published',
                'type' => 'exercise',
            );
            $exerciseTasks = $this->getTaskService()->searchTasks($conditions, null, 0, 1);
            if (!$exerciseTasks) {
                return $this->error('404', '该练习不存在!');
            }
            $exerciseTask = $exerciseTasks[0];

            $activity = $this->getActivityService()->getActivity($exerciseTask['activityId']);
            $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($activity['mediaId'], $activity['mediaType']);
        } else {
            $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($id, 'exercise');

            $conditions = array(
                'mediaId' => $exercise['id'],
                'mediaType' => 'exercise',
                'fromCourseId' => $exercise['courseId'],
            );
            $activities = $this->getActivityService()->search($conditions, null, 0, 1);

            if (!$activities) {
                return $this->error('404', '该练习任务不存在!');
            }
            $activity = $activities[0];
        }
        $exercise['lessonId'] = $activity['id'];

        $access = $this->getCourseService()->canLearnCourse($exercise['courseId']);
        if (AccessorInterface::SUCCESS !== $access['code']) {
            return $this->error($access['code'], $access['msg']);
        }

        if (empty($exercise)) {
            return $this->error('404', '该练习不存在!');
        }

        $course = $this->getCourseService()->getCourse($exercise['courseId']);
        $exercise['courseTitle'] = $course['title'];
        $exercise['lessonTitle'] = $activity['title'];
        $exercise['description'] = $activity['title'];

        $result = $this->getTestpaperService()->startTestpaper($exercise['id'], array('lessonId' => $activity['id'], 'courseId' => $exercise['courseId']));

        if (empty($result)) {
            return $this->error('404', '该练习结果不存在!');
        }

        if ('lesson' != $idType) {
            $builder = $this->getTestpaperService()->getTestpaperBuilder('exercise');
            $items = $builder->showTestItems($exercise['id']);

            $exercise['items'] = $this->filterItem($items, null);
        }

        return $this->filter($exercise);
    }

    public function result(Application $app, Request $request, $id)
    {
        $user = $this->getCurrentUser();
        $exercise = $this->getTestpaperService()->getTestpaperByIdAndType($id, 'exercise');

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
        $activity = $activities[0];

        $result = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $exercise['id'], $exercise['courseId'], $activity['id'], 'exercise');

        if (!$result) {
            return $this->error('404', '不存在该练习的答题结果记录!');
        }

        $course = $this->getCourseService()->getCourse($exercise['courseId']);
        $exercise['courseTitle'] = $course['title'];
        $exercise['lessonTitle'] = $exercise['name'];
        $exercise['description'] = $exercise['description'];

        $builder = $this->getTestpaperService()->getTestpaperBuilder('exercise');
        $items = $builder->showTestItems($exercise['id'], $result['id']);

        $itemResults = $this->getTestpaperService()->findItemResultsByResultId($result['id']);
        $itemResults = ArrayToolkit::index($itemResults, 'questionId');

        $exercise['items'] = $this->filterItem($items, $itemResults);

        return $this->filterResult($exercise);
    }

    private function filterItem($items, $itemSetResults)
    {
        $newItmes = array();
        $materialMap = array();
        foreach ($items as $item) {
            $item = $this->filterItemFields($item, $itemSetResults);

            $item['items'] = array();
            if ('material' == $item['type']) {
                $subs = empty($item['subs']) ? array() : $item['subs'];
                foreach ($subs as &$sub) {
                    $sub = $this->filterItemFields($sub, $itemSetResults);
                }

                $item['items'] = $subs;
                $item['result'] = null;
            }

            if ($itemSetResults && !empty($itemSetResults[$item['id']])) {
                $itemResult = $itemSetResults[$item['id']];
                if (!empty($itemResult['answer'][0])) {
                    $itemResult['answer'][0] = $this->filterHtml($itemResult['answer'][0]);
                }

                if (!empty($itemResult['teacherSay'])) {
                    $itemResult['teacherSay'] = $this->filterHtml($itemResult['teacherSay']);
                }

                $item['result'] = $itemResult;
            }

            unset($item['subs']);

            $newItmes[$item['id']] = $item;
        }

        return array_values($newItmes);
    }

    public function filterQuestion(&$res)
    {
        foreach ($res as &$value) {
            $value['questionType'] = $value['question']['type'];
            unset($value['question']);
            if (array_key_exists('subItems', $value)) {
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

    public function filterItemFields($item, $itemResults)
    {
        if (empty($item)) {
            return array();
        }

        $item = ArrayToolkit::parts($item, array('id', 'type', 'stem', 'answer', 'analysis', 'metas', 'difficulty', 'parentId', 'subs', 'testResult'));

        $item['stem'] = $this->filterHtml($item['stem']);
        $item['analysis'] = $this->filterHtml($item['analysis']);
        $item['metas'] = empty($item['metas']) ? array() : $item['metas'];

        if (isset($item['metas']['choices'])) {
            $metas = array_values($item['metas']['choices']);
            $self = $this;
            $item['metas'] = array_map(function ($choice) use ($self) {
                return $self->filterHtml($choice);
            }, $metas);
        }

        if (!empty($item['testResult'])) {
            $item['result'] = $item['testResult'];
        }

        $item['answer'] = $this->filterAnswer($item, $itemResults);

        return $item;
    }

    public function filterResult(&$res)
    {
        $res = ArrayToolkit::parts($res, array('id', 'courseId', 'lessonId', 'description', 'itemCount', 'items', 'courseTitle', 'lessonTitle'));
        $items = $res['items'];
        foreach ($items as &$item) {
            unset($item['result']['score']);
            unset($item['result']['missScore']);
            unset($item['result']['question']);
            $item['result'] = empty($item['result']) ? (object) array() : $item['result'];
            if (!empty($item['items'])) {
                foreach ($item['items'] as &$subItem) {
                    $subItem['result'] = empty($subItem['result']) ? (object) array() : $subItem['result'];
                }
            }
        }
        $res['items'] = array_reverse($items);

        return $res;
    }

    private function filterAnswer($item, $itemSetResults)
    {
        if (empty($itemSetResults)) {
            if ('fill' == $item['type']) {
                return array_map(function ($answer) {
                    return '';
                }, $item['answer']);
            }

            return null;
        }

        return $this->coverAnswer($item['answer']);
    }

    private function coverAnswer($answer)
    {
        if (is_array($answer)) {
            $self = $this;
            $answer = array_map(function ($answerValue) use ($self) {
                if (is_array($answerValue)) {
                    return implode('|', $answerValue);
                }

                return $self->filterHtml($answerValue);
            }, $answer);

            return $answer;
        }

        return array();
    }

    public function getByLesson(Application $app, Request $request, $id)
    {
        $exerciseService = $this->getExerciseService();
        $exercise = $exerciseService->getExerciseByLessonId($id);
        if (empty($exercise)) {
            return $this->error('404', '该课时不存在练习!');
        }

        $itemSet = $exerciseService->getItemSetByExerciseId($exercise['id']);

        return array_merge($exercise, $itemSet);
    }

    private function getquestionTypeRangeStr(array $questionTypeRange)
    {
        $questionTypeRangeStr = '';
        foreach ($questionTypeRange as $key => $questionType) {
            $questionTypeRangeStr .= "'{$questionType}',";
        }

        return substr($questionTypeRangeStr, 0, -1);
    }

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper:TestpaperService');
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question:QuestionService');
    }

    protected function getCourseService()
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
