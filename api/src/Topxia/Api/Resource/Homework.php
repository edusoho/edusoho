<?php

namespace Topxia\Api\Resource;

use AppBundle\Common\ArrayToolkit;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Course\Service\CourseService;
use Biz\Testpaper\Wrapper\TestpaperWrapper;
use Codeages\Biz\ItemBank\Answer\Service\AnswerQuestionReportService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerSceneService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

class Homework extends BaseResource
{
    public function get(Application $app, Request $request, $id)
    {
        $idType = $request->query->get('_idType');
        if ('lesson' == $idType) {
            $task = $this->getTaskService()->getTask($id);
            $course = $this->getCourseService()->getCourse($task['courseId']);

            if (CourseService::DEFAULT_COURSE_TYPE != $course['courseType']) {
                return $this->error('404', '该作业不存在!');
            }

            //只为兼容移动端学习引擎2.0以前的版本，之后需要修改
            $conditions = [
                'categoryId' => $task['categoryId'],
                'status' => 'published',
                'type' => 'homework',
            ];
            $homeworkTasks = $this->getTaskService()->searchTasks($conditions, null, 0, 1);
            if (!$homeworkTasks) {
                return $this->error('404', '该作业不存在!');
            }
            $homeworkTask = $homeworkTasks[0];

            $activity = $this->getActivityService()->getActivity($homeworkTask['activityId'], true);
            $assessmentId = $activity['ext']['assessmentId'];
        } else {
            $assessmentId = $id;
        }

        $assessment = $this->getAssessmentService()->showAssessment($assessmentId);
        if (empty($assessment)) {
            return $this->error('404', '该作业不存在!');
        }

        $homeworkActivity = $this->getHomeworkActivityService()->getByAssessmentId($assessment['id']);
        $conditions = [
            'mediaId' => $homeworkActivity['id'],
            'mediaType' => 'homework',
        ];
        $activities = $this->getActivityService()->search($conditions, null, 0, 1);
        if (!$activities) {
            return $this->error('404', '该作业任务不存在!');
        }

        $canTakeCourse = $this->getCourseService()->canTakeCourse($activities[0]['fromCourseId']);
        if (!$canTakeCourse) {
            return $this->error('500', '无权限访问!');
        }

        $course = $this->getCourseService()->getCourse($activities[0]['fromCourseId']);
        $testpaperWrapper = new TestpaperWrapper();
        $scene = $this->getAnswerSceneService()->get($homeworkActivity['answerSceneId']);
        $homework = $testpaperWrapper->wrapTestpaper($assessment, $scene);
        $homework['courseTitle'] = $course['title'];
        $homework['lessonTitle'] = $homework['name'];
        $homework['lessonId'] = $id;

        if ('lesson' != $idType) {
            $items = $testpaperWrapper->wrapTestpaperItems($assessment, []);
            $homework['items'] = $this->filterItem($items, null, 0, 0);
        }

        return $this->filter($homework);
    }

    public function result(Application $app, Request $request, $id)
    {
        $currentUser = $this->getCurrentUser();
        $answerRecord = $this->getAnswerRecordService()->get($id);
        if (empty($answerRecord)) {
            return $this->error('404', '作业结果不存在！');
        }

        $homeworkActivity = $this->getHomeworkActivityService()->getByAnswerSceneId($answerRecord['answer_scene_id']);
        if (empty($homeworkActivity)) {
            return $this->error('404', '作业任务不存在！');
        }

        $conditions = [
            'mediaId' => $homeworkActivity['id'],
            'mediaType' => 'homework',
        ];
        $activities = $this->getActivityService()->search($conditions, null, 0, 1);
        if (!$activities) {
            return $this->error('404', '作业任务不存在!');
        }
        $activity = $activities[0];

        $assessment = $this->getAssessmentService()->showAssessment($homeworkActivity['assessmentId']);
        if (empty($assessment)) {
            return $this->error('404', '作业不存在！');
        }

        $canTakeCourse = $this->getCourseService()->canTakeCourse($activity['fromCourseId']);
        if (!$canTakeCourse) {
            return $this->error('500', '无权限访问!');
        }

        if (empty($currentUser) || ('doing' === $answerRecord['status'] && ($answerRecord['user_id'] != $currentUser['id']))) {
            return $this->error('500', '不能查看该作业结果！');
        }

        if (!in_array($answerRecord['status'], ['finished', 'reviewing'])) {
            return $this->error('500', '作业还未做完！');
        }

        $course = $this->getCourseService()->getCourse($activity['fromCourseId']);
        $testpaperWrapper = new TestpaperWrapper();
        $scene = $this->getAnswerSceneService()->get($homeworkActivity['answerSceneId']);
        $homework = $testpaperWrapper->wrapTestpaper($assessment, $scene);
        $homework['courseTitle'] = $course['title'];
        $homework['lessonTitle'] = $homework['name'];

        $questionReports = $this->getAnswerQuestionReportService()->findByAnswerRecordId($answerRecord['id']);
        $items = $testpaperWrapper->wrapTestpaperItems($assessment, $questionReports);
        $homework['items'] = $this->filterItem($items, $questionReports, $homework['id'], $answerRecord['id']);

        return $this->filter($homework);
    }

    private function filterItem($items, $itemSetResults, $homeworkId, $resultId)
    {
        $newItmes = [];
        foreach ($items as $item) {
            $item = $this->filterQuestion($item, $itemSetResults, $homeworkId, $resultId);
            if ('material' == $item['type']) {
                $subs = empty($item['subs']) ? [] : array_values($item['subs']);
                foreach ($subs as &$subQuestion) {
                    $subQuestion = $this->filterQuestion($subQuestion, $itemSetResults, $homeworkId, $resultId);
                }
                $item['items'] = $subs;
            } else {
                $item['items'] = [];
            }

            $newItmes[$item['id']] = $item;
        }

        return array_values($newItmes);
    }

    protected function filterQuestion($question, $questionResults, $homeworkId, $resultId)
    {
        $question = ArrayToolkit::parts($question, ['id', 'type', 'stem', 'answer', 'analysis', 'metas', 'difficulty', 'parentId', 'subs', 'testResult']);
        $question['stem'] = $this->filterHtml($question['stem']);
        $question['analysis'] = $this->filterHtml($question['analysis']);

        if (empty($question['metas'])) {
            $question['metas'] = [];
        }
        if (isset($question['metas']['choices'])) {
            $metas = array_values($question['metas']['choices']);

            $self = $this;
            $question['metas'] = array_map(function ($choice) use ($self) {
                return $self->filterHtml($choice);
            }, $metas);
        }

        $question['answer'] = $this->filterAnswer($question, $questionResults);

        if ($questionResults && !empty($question['testResult'])) {
            $itemResult = $question['testResult'];
            if (!empty($itemResult['answer'][0])) {
                $itemResult['answer'][0] = $this->filterHtml($itemResult['answer'][0]);
            }

            if (!empty($itemResult['teacherSay'])) {
                $itemResult['teacherSay'] = $this->filterHtml($itemResult['teacherSay']);
            }

            $question['result'] = $itemResult;
        } else {
            $question['result'] = [
                'id' => '0',
                'itemId' => '0',
                'testId' => $homeworkId,
                'resultId' => $resultId,
                'answer' => [],
                'questionId' => $question['id'],
                'status' => 'noAnswer',
                'score' => '0',
                'teacherSay' => null,
                'type' => $question['type'],
            ];
        }

        $question['stem'] = $this->coverDescription($question['stem']);

        return $question;
    }

    public function filter($res)
    {
        $res = ArrayToolkit::parts($res, ['id', 'courseId', 'lessonId', 'description', 'itemCount', 'items', 'courseTitle', 'lessonTitle']);

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

            return;
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

    private function coverDescription($stem)
    {
        $ext = $this;
        $stem = preg_replace_callback('/\[image\](.*?)\[\/image\]/i', function ($matches) use ($ext) {
            $url = $ext->getFileUrl($matches[1]);

            return "<img src='{$url}' />";
        }, $stem);

        return $stem;
    }

    protected function canCheckHomework($homework)
    {
        try {
            $this->getCourseService()->tryManageCourse($homework['courseId']);

            return true;
        } catch (\Exception $e) {
            return false;
        }
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
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->getServiceKernel()->createService('Activity:HomeworkActivityService');
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
