<?php

namespace Topxia\Api\Resource;

use AppBundle\Common\ArrayToolkit;
use Biz\Accessor\AccessorInterface;
use Biz\Activity\Service\ExerciseActivityService;
use Biz\Course\Service\CourseService;
use Biz\Testpaper\Wrapper\TestpaperWrapper;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Exercise extends BaseResource
{
    public function get(Application $app, Request $request, $id)
    {
        $idType = $request->query->get('_idType');
        $user = $this->getCurrentUser();
        if ('lesson' == $idType) {
            $task = $this->getTaskService()->getTask($id);
            $course = $this->getCourseService()->getCourse($task['courseId']);

            if (CourseService::DEFAULT_COURSE_TYPE != $course['courseType']) {
                return $this->error('404', '该练习不存在!');
            }

            //只为兼容移动端学习引擎2.0以前的版本，之后需要修改
            $conditions = [
                'categoryId' => $task['categoryId'],
                'status' => 'published',
                'type' => 'exercise',
            ];
            $exerciseTasks = $this->getTaskService()->searchTasks($conditions, null, 0, 1);
            if (!$exerciseTasks) {
                return $this->error('404', '该练习不存在!');
            }
            $exerciseTask = $exerciseTasks[0];

            $activity = $this->getActivityService()->getActivity($exerciseTask['activityId'], true);
            $assessment = $this->createAssessment($activity['title'], $activity['ext']['drawCondition']['range'], [$activity['ext']['drawCondition']['section']]);
            $assessment = $this->getAssessmentService()->showAssessment($assessment['id']);
            $scene = $this->getAnswerSceneService()->get($activity['ext']['answerSceneId']);
        } else {
            $exerciseActivity = $this->getExerciseActivityService()->getActivity($id);
            if (empty($exerciseActivity)) {
                return $this->error('404', '该练习不存在!');
            }
            $conditions = [
                'mediaId' => $exerciseActivity['id'],
                'mediaType' => 'exercise',
            ];
            $activities = $this->getActivityService()->search($conditions, null, 0, 1);
            if (!$activities) {
                return $this->error('404', '该练习任务不存在!');
            }
            $activity = $activities[0];

            $scene = $this->getAnswerSceneService()->get($exerciseActivity['answerSceneId']);
            if (empty($scene)) {
                return $this->error('404', '该练习不存在!');
            }
            $answerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($exerciseActivity['answerSceneId'], $user['id']);
            if (empty($answerRecord) || AnswerService::ANSWER_RECORD_STATUS_FINISHED == $answerRecord['status']) {
                $assessment = $this->createAssessment($activity['title'], $exerciseActivity['drawCondition']['range'], [$exerciseActivity['drawCondition']['section']]);
                $assessment = $this->getAssessmentService()->showAssessment($assessment['id']);
            } else {
                $assessment = $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']);
            }
        }
        $testpaperWrapper = new TestpaperWrapper();
        $exercise = $testpaperWrapper->wrapTestpaper($assessment);
        $exercise['lessonId'] = $activity['id'];

        $access = $this->getCourseService()->canLearnCourse($activity['fromCourseId']);
        if (AccessorInterface::SUCCESS !== $access['code']) {
            return $this->error($access['code'], $access['msg']);
        }

        if (empty($exercise)) {
            return $this->error('404', '该练习不存在!');
        }

        $course = $this->getCourseService()->getCourse($activity['fromCourseId']);
        $exercise['courseTitle'] = $course['title'];
        $exercise['lessonTitle'] = $activity['title'];
        $exercise['description'] = $activity['title'];

        if (empty($answerRecord) || AnswerService::ANSWER_RECORD_STATUS_FINISHED == $answerRecord['status']) {
            $answerRecord = $this->getAnswerService()->startAnswer($scene['id'], $exercise['id'], $user['id']);
        }

        if ('lesson' != $idType) {
            $items = $testpaperWrapper->wrapTestpaperItems($assessment, []);

            $exercise['items'] = $this->filterItem($items, null);
            $exercise['id'] = $id;
        }

        return $this->filter($exercise);
    }

    public function result(Application $app, Request $request, $id)
    {
        $user = $this->getCurrentUser();
        $mediaId = $id;
        $exerciseActivity = $this->getExerciseActivityService()->getActivity($mediaId);
        if (empty($exerciseActivity)) {
            return $this->error('404', '该练习不存在!');
        }

        $answerRecord = $this->getAnswerRecordService()->getLatestAnswerRecordByAnswerSceneIdAndUserId($exerciseActivity['answerSceneId'], $user['id']);
        if (empty($answerRecord)) {
            return $this->error('404', '不存在该练习的答题结果记录!');
        }

        $assessment = $this->getAssessmentService()->showAssessment($answerRecord['assessment_id']);

        $conditions = [
            'mediaId' => $exerciseActivity['id'],
            'mediaType' => 'exercise',
        ];
        $activities = $this->getActivityService()->search($conditions, null, 0, 1);
        if (!$activities) {
            return $this->error('404', '该练习任务不存在!');
        }
        $activity = $activities[0];

        $canTakeCourse = $this->getCourseService()->canTakeCourse($activity['fromCourseId']);
        if (!$canTakeCourse) {
            return $this->error('500', '无权限访问!');
        }

        $testpaperWrapper = new TestpaperWrapper();
        $scene = $this->getAnswerSceneService()->get($exerciseActivity['answerSceneId']);
        $exercise = $testpaperWrapper->wrapTestpaper($assessment, $scene);
        $course = $this->getCourseService()->getCourse($activity['fromCourseId']);
        $exercise['courseTitle'] = $course['title'];
        $exercise['lessonTitle'] = $exercise['name'];
        $exercise['description'] = $exercise['description'];

        $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecord['id']);
        $items = $testpaperWrapper->wrapTestpaperItems($assessment, $questionReports);

        $exercise['items'] = $this->filterItem($items, $questionReports);

        return $this->filterResult($exercise);
    }

    protected function createAssessment($name, $range, $sections)
    {
        $sections = $this->getAssessmentService()->drawItems($range, $sections);
        $assessment = [
            'name' => $name,
            'displayable' => 0,
            'description' => '',
            'bank_id' => $range['bank_id'],
            'sections' => $sections,
        ];

        $assessment = $this->getAssessmentService()->createAssessment($assessment);

        $this->getAssessmentService()->openAssessment($assessment['id']);

        return $assessment;
    }

    private function filterItem($items, $itemSetResults)
    {
        krsort($items);
        $newItmes = [];
        foreach ($items as $item) {
            $item = $this->filterItemFields($item, $itemSetResults);

            $item['items'] = [];
            if ('material' == $item['type']) {
                $subs = empty($item['subs']) ? [] : array_values($item['subs']);
                foreach ($subs as &$sub) {
                    $sub = $this->filterItemFields($sub, $itemSetResults);
                }

                $item['items'] = $subs;
                $item['result'] = null;
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
        $res = ArrayToolkit::parts($res, ['id', 'courseId', 'lessonId', 'description', 'itemCount', 'items', 'courseTitle', 'lessonTitle']);

        return $res;
    }

    public function filterItemFields($item, $itemResults)
    {
        if (empty($item)) {
            return [];
        }

        $item = ArrayToolkit::parts($item, ['id', 'type', 'stem', 'answer', 'analysis', 'metas', 'difficulty', 'parentId', 'subs', 'testResult']);

        $item['stem'] = $this->filterHtml($item['stem']);
        $item['analysis'] = $this->filterHtml($item['analysis']);
        $item['metas'] = empty($item['metas']) ? [] : $item['metas'];

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

        if ($itemResults && !empty($item['testResult'])) {
            $itemResult = $item['testResult'];
            if (!empty($itemResult['answer'][0])) {
                $itemResult['answer'][0] = $this->filterHtml($itemResult['answer'][0]);
            }

            if (!empty($itemResult['teacherSay'])) {
                $itemResult['teacherSay'] = $this->filterHtml($itemResult['teacherSay']);
            }

            $item['result'] = $itemResult;
        }

        return $item;
    }

    public function filterResult(&$res)
    {
        $res = ArrayToolkit::parts($res, ['id', 'courseId', 'lessonId', 'description', 'itemCount', 'items', 'courseTitle', 'lessonTitle']);
        $items = $res['items'];
        foreach ($items as &$item) {
            unset($item['result']['score']);
            unset($item['result']['missScore']);
            unset($item['result']['question']);
            $item['result'] = empty($item['result']) ? (object) [] : $item['result'];
            if (!empty($item['items'])) {
                foreach ($item['items'] as &$subItem) {
                    $subItem['result'] = empty($subItem['result']) ? (object) [] : $subItem['result'];
                }
            }
        }
        $res['items'] = $items;

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

        return [];
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

    /**
     * @return ExerciseActivityService
     */
    protected function getExerciseActivityService()
    {
        return $this->getServiceKernel()->createService('Activity:ExerciseActivityService');
    }

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->getServiceKernel()->createService('ItemBank:Assessment:AssessmentService');
    }

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->getServiceKernel()->createService('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerService
     */
    protected function getAnswerService()
    {
        return $this->getServiceKernel()->createService('ItemBank:Answer:AnswerService');
    }

    /**
     * @return AnswerSceneService
     */
    protected function getAnswerSceneService()
    {
        return $this->getServiceKernel()->createService('ItemBank:Answer:AnswerSceneService');
    }

    /**
     * @return AnswerQuestionReportService
     */
    protected function getAnswerQuestionReportService()
    {
        return $this->getServiceKernel()->createService('ItemBank:Answer:AnswerQuestionReportService');
    }
}
