<?php

namespace Topxia\MobileBundleV2\Processor\Impl;

use AppBundle\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use Topxia\MobileBundleV2\Processor\BaseProcessor;
use Topxia\MobileBundleV2\Processor\TestpaperProcessor;

class TestpaperProcessorImpl extends BaseProcessor implements TestpaperProcessor
{
    public function reDoTestpaper()
    {
        $testId = $this->getParam('testId');
        $targetType = $this->getParam('targetType');
        $targetId = $this->getParam('targetId');

        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $task = $this->getTaskService()->getTask($targetId);
        if (!$task) {
            return $this->createErrorResponse('error', '试卷所属课时不存在！');
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        if (empty($testpaper)) {
            return $this->createErrorResponse('error', '试卷不存在！或已删除!');
        }

        if ('draft' == $testpaper['status']) {
            return $this->createErrorResponse('error', '该试卷未发布，如有疑问请联系老师！!');
        }

        if ('closed' == $testpaper['status']) {
            return $this->createErrorResponse('error', '该试卷已关闭，如有疑问请联系老师！!');
        }

        $course = $this->getCourseService()->getCourse($task['courseId']);

        if (empty($course)) {
            return $this->createErrorResponse('error', '试卷所属课程不存在！');
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            return $this->createErrorResponse('error', '不是试卷所属课程老师或学生');
        }

        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        $testpaperResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $testpaper['id'], $activity['fromCourseSetId'], $activity['id'], $testpaper['type']);

        if ($testpaperActivity['doTimes'] && $testpaperResult && 'finished' == $testpaperResult['status']) {
            return $this->createErrorResponse('error', '该试卷只能考一次，不能再考！');
        } elseif ($testpaperActivity['redoInterval']) {
            $nextDoTime = $testpaperResult['checkedTime'] + $testpaperActivity['redoInterval'] * 3600;
            if ($nextDoTime > time()) {
                return array('result' => false, 'message' => $this->getServiceKernel()->trans('教师设置了重考间隔，请在'.date('Y-m-d H:i:s', $nextDoTime).'之后再考！'));

                return $this->createErrorResponse('error', '教师设置了重考间隔，请在'.date('Y-m-d H:i:s', $nextDoTime).'之后再考！');
            }
        }

        if (!$testpaperResult || ($testpaperResult && 'finished' == $testpaperResult['status'])) {
            $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], array('lessonId' => $activity['id'], 'courseId' => $activity['fromCourseId'], 'limitedTime' => $testpaperActivity['limitedTime']));
        }

        $items = $this->showTestpaperItems($testpaper['id'], $testpaperResult['id']);
        $testpaper['metas']['question_type_seq'] = array_keys($items);

        return array(
            'testpaperResult' => $testpaperResult,
            'testpaper' => $testpaper,
            'items' => $this->coverTestpaperItems($items, 0),
        );
    }

    public function favoriteQuestion()
    {
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $id = $this->getParam('id'); // => questionId
        $action = $this->getParam('action');
        $targetType = $this->getParam('targetType');
        $targetId = $this->getParam('targetId');
        $target = $targetType.'-'.$targetId;

        if ('favorite' == $action) {
            $favorite = array(
                'questionId' => $id,
                'targetId' => $targetId,
                'targetType' => $targetType,
            );
            $this->getQuestionService()->createFavoriteQuestion($favorite);
        } else {
            $conditions = array(
                'questionId' => $id,
                'targetId' => $targetId,
                'targetType' => $targetType,
            );
            $userFavorits = $this->getQuestionService()->searchFavoriteQuestions($conditions, null, 0, 1);
            if ($userFavorits) {
                $this->getQuestionService()->deleteFavoriteQuestion($userFavorits[0]['id']);
            }
        }

        return true;
    }

    public function myTestpaper()
    {
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $start = (int) $this->getParam('start', 0);
        $limit = (int) $this->getParam('limit', 10);

        $conditions = array('userId' => $user['id']);
        $total = $this->getTestpaperService()->searchTestpaperResultsCount($conditions);

        $testpaperResults = $this->getTestpaperService()->searchTestpaperResults(
            $conditions,
            array('beginTime' => 'desc'),
            $start,
            $limit
        );

        $testpapersIds = ArrayToolkit::column($testpaperResults, 'testId');

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpapersIds);
        $testpapers = ArrayToolkit::index($testpapers, 'id');

        $targets = ArrayToolkit::column($testpapers, 'target');
        $courseIds = array_map(function ($target) {
            $course = explode('/', $target);
            $course = explode('-', $course[0]);

            return $course[1];
        }, $targets);

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $data = array(
            'myTestpaperResults' => $this->filterMyTestpaperResults($testpaperResults, $testpapersIds),
            'myTestpapers' => $this->filterMyTestpaper($testpapers),
            'courses' => $this->filterMyTestpaperCourses($courses),
        );

        return array(
            'start' => $start,
            'total' => $total,
            'limit' => $limit,
            'data' => $data,
        );
    }

    private function filterMyTestpaperResults($testpaperResults, $testIds)
    {
        $results = $testpaperResults;
        foreach ($testpaperResults as $key => $value) {
            if (!in_array($value['testId'], $testIds)) {
                unset($results[$key]);
            } else {
                $results[$key]['beginTime'] = date('Y-m-d H:i:s', $value['beginTime']);
                $results[$key]['endTime'] = date('Y-m-d H:i:s', $value['endTime']);
                if (0 != $results[$key]['updateTime']) {
                    $results[$key]['updateTime'] = date('Y-m-d H:i:s', $value['updateTime']);
                }
                $results[$key]['checkedTime'] = date('Y-m-d H:i:s', $value['checkedTime']);
            }
        }

        return $results;
    }

    private function filterMyTestpaper($testpapers)
    {
        return array_map(function ($testpaper) {
            unset($testpaper['description']);
            unset($testpaper['metas']);

            return $testpaper;
        }, $testpapers);
    }

    private function filterMyTestpaperCourses($courses)
    {
        $courses = $this->controller->filterCourses($courses);

        return array_map(function ($course) {
            unset($course['about']);
            unset($course['teachers']);
            unset($course['goals']);
            unset($course['audiences']);
            unset($course['subtitle']);

            return $course;
        }, $courses);
    }

    public function uploadQuestionImage()
    {
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }
        $url = '';
        try {
            $file = $this->request->files->get('file');
            $group = $this->getParam('group', 'course');
            $record = $this->getFileService()->uploadFile($group, $file);
            $url = $this->controller->get('web.twig.extension')->getFilePath($record['uri']);
        } catch (\Exception $e) {
            $url = '';
        }

        if ($this->isAbsoluteUrl($url)) {
            $url = $this->request->getScheme().':'.ltrim($url, ':');
        } else {
            $url = $this->getBaseUrl().$url;
        }

        return $url;
    }

    public function finishTestpaper()
    {
        $user = $this->controller->getUserByToken($this->request);
        $id = $this->getParam('id');

        if (!$user->isLogin()) {
            return false;
        }
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);

        if (!empty($testpaperResult) && !in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return true;
        }

        if ($user['id'] != $testpaperResult['userId']) {
            return false;
        }

        $data = $this->request->request->all();

        $this->getTestpaperService()->finishTest($testpaperResult['id'], $data);

        return true;
    }

    public function showTestpaper()
    {
        $id = $this->getParam('id');
        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);
        if (!$testpaperResult) {
            return $this->createErrorResponse('error', '试卷不存在');
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        $items = $this->showTestpaperItems($testpaper['id'], $testpaperResult['id']);
        $testpaper['metas']['question_type_seq'] = array_keys($items);

        return array(
            'testpaperResult' => $testResult,
            'testpaper' => $testpaper,
            'items' => $this->coverTestpaperItems($items, 1),
        );
    }

    public function doTestpaper()
    {
        $testId = $this->getParam('testId');
        $targetType = $this->getParam('targetType'); // => lesson
        $targetId = $this->getParam('targetId'); // => lessonId

        $user = $this->controller->getUserByToken($this->request);
        if (!$user->isLogin()) {
            return $this->createErrorResponse('not_login', '您尚未登录，不能查看该课时');
        }

        $task = $this->getTaskService()->getTask($targetId);
        if (!$task) {
            return $this->createErrorResponse('error', '试卷所属课时不存在！');
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        $course = $this->getCourseService()->getCourse($task['courseId']);

        if (empty($course)) {
            return $this->createErrorResponse('error', '试卷所属课程不存在！');
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            return $this->createErrorResponse('error', '不是试卷所属课程老师或学生');
        }

        if (empty($testpaper)) {
            return $this->createErrorResponse('error', '试卷不存在！或已删除!');
        }

        $activity = $this->getActivityService()->getActivity($task['activityId']);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        $testpaperResult = $this->getTestpaperService()->getUserLatelyResultByTestId($user['id'], $testpaperActivity['mediaId'], $activity['fromCourseId'], $activity['id'], $activity['mediaType']);

        $items = $this->showTestpaperItems($testpaper['id']);
        $testpaper['metas']['question_type_seq'] = array_keys($items);

        if (empty($testpaperResult)) {
            if ('draft' == $testpaper['status']) {
                return $this->createErrorResponse('error', '该试卷未发布，如有疑问请联系老师！!');
            }
            if ('closed' == $testpaper['status']) {
                return $this->createErrorResponse('error', '该试卷已关闭，如有疑问请联系老师！!');
            }

            $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], array('lessonId' => $activity['id'], 'courseId' => $activity['fromCourseId'], 'limitedTime' => $testpaperActivity['limitedTime']));

            return array(
                'testpaperResult' => $testpaperResult,
                'testpaper' => $testpaper,
                'items' => $this->coverTestpaperItems($items, 1),
            );
        }
        if (in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return array(
                'testpaperResult' => $testpaperResult,
                'testpaper' => $testpaper,
                'items' => $this->coverTestpaperItems($items, 1),
            );
        } else {
            return $this->createErrorResponse('error', '试卷正在批阅！不能重新考试!');
        }
    }

    public function getTestpaperResult()
    {
        $answerShowMode = $this->controller->setting('questions.testpaper_answers_show_mode', 'submitted');

        // 不显示题目
        if ('hide' == $answerShowMode) {
            return $this->createErrorResponse('error', '网校已关闭交卷后答案解析的显示!');
        }

        $id = $this->getParam('id');
        $user = $this->controller->getUserByToken($this->request);
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);
        if (!$testpaperResult || $testpaperResult['userId'] != $user['id']) {
            return $this->createErrorResponse('error', '不可以访问其他学生的试卷哦!');
        }

        //客观题自动批阅完后先显示答案解析
        if ('reviewed' == $answerShowMode && 'finished' != $testpaperResult['status']) {
            return $this->createErrorResponse('error', '试卷正在批阅，需要批阅完后才能显示答案解析!');
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        $activity = $this->getActivityService()->getActivity($testpaperResult['lessonId']);
        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        if ($testpaperResult['userId'] != $user['id']) {
            $course = $this->controller->getCourseService()->tryManageCourse($testpaperResult['courseId']);
        }

        if (empty($course) && $testpaperResult['userId'] != $user['id']) {
            return $this->createErrorResponse('error', '不可以访问其他学生的试卷哦!');
        }

        $accuracy = $this->getTestpaperService()->makeAccuracy($testpaperResult['id']);

        $favorites = $this->getQuestionService()->findUserFavoriteQuestions($user['id']);

        $items = $this->showTestpaperItems($testpaper['id'], $testpaperResult['id']);

        $testpaper['metas']['question_type_seq'] = array_keys($items);

        return array(
            'testpaper' => $testpaper,
            'items' => $this->filterResultItems($items, true),
            'accuracy' => $accuracy,
            'paperResult' => $testpaperResult,
            'favorites' => ArrayToolkit::column($favorites, 'questionId'),
        );
    }

    private function filterResultItems($items, $isShowTestResult)
    {
        $controller = $this;
        $newItems = array_map(function ($item) {
            return array_values($item);
        }, $items);

        return $this->coverTestpaperItems($newItems, $isShowTestResult);
    }

    private function getTestpaperItem($testpaperResult, $isShowTestResult = false)
    {
        $result = $this->getTestpaperService()->showTestpaper($testpaperResult['id']);
        $items = $result['formatItems'];

        return $this->coverTestpaperItems($items, $isShowTestResult);
    }

    public function filterQuestionStem($stem)
    {
        $ext = $this;
        $baseUrl = $this->request->getSchemeAndHttpHost();
        $stem = preg_replace_callback('/\[image\](.*?)\[\/image\]/i', function ($matches) use ($baseUrl, $ext) {
            $url = $ext->controller->get('web.twig.extension')->getFileUrl($matches[1]);
            $url = $baseUrl.$url;

            return "<img src='{$url}' />";
        }, $stem);

        return $stem;
    }

    private function coverTestpaperItems($items, $isShowTestResult)
    {
        $controller = $this;
        $result = array_map(function ($item) use ($controller, $isShowTestResult) {
            $item = array_map(function ($itemValue) use ($controller, $isShowTestResult) {
                $question = $itemValue['question'];

                if (isset($question['isDeleted']) && true == $question['isDeleted']) {
                    return array();
                }
                if (isset($itemValue['items'])) {
                    $filterItems = array_values($itemValue['items']);
                    $itemValue['items'] = array_map(function ($filterItem) use ($controller, $isShowTestResult) {
                        return $controller->filterMetas($filterItem, $isShowTestResult);
                    }, $filterItems);
                }

                $itemValue = $controller->filterMetas($itemValue, $isShowTestResult);

                return $itemValue;
            }, $item);
            if ($controller->arrayIsEmpty($item)) {
                return;
            }

            return array_values($item);
        }, $items);

        foreach ($result as $key => $value) {
            if (empty($value)) {
                $result[$key] = array();
            }

            foreach ($result[$key] as $k => $v) {
                if (empty($v)) {
                    unset($result[$key][$k]);
                }
            }

            $result[$key] = array_values($result[$key]);

            uasort(
                $result[$key],
                function ($item1, $item2) {
                    return $item1['seq'] > $item2['seq'];
                }
            );
        }

        return $result;
    }

    public function arrayIsEmpty($array)
    {
        foreach ($array as $key => $value) {
            if (!empty($value)) {
                return false;
            }
        }

        return true;
    }

    public function filterMetas($itemValue, $isShowTestResult)
    {
        $container = $this->getContainer();
        $question = $itemValue['question'];
        $question['stem'] = $this->controller->convertAbsoluteUrl($container->get('request'), $question['stem']);
        $question['analysis'] = $this->controller->convertAbsoluteUrl($container->get('request'), $question['analysis']);

        if (!$isShowTestResult && isset($question['testResult'])) {
            unset($question['testResult']);
        }

        if (isset($question['testResult'])) {
            if (!empty($question['testResult']['answer'][0])) {
                $question['testResult']['answer'][0] = $this->controller->convertAbsoluteUrl($container->get('request'), $question['testResult']['answer'][0]);
            }

            if (!empty($question['testResult']['teacherSay'])) {
                $question['testResult']['teacherSay'] = $this->controller->convertAbsoluteUrl($container->get('request'), $question['testResult']['teacherSay']);
            }
        }

        $itemValue['question'] = $question;
        $self = $this;
        if (isset($question['metas'])) {
            $metas = $question['metas'];
            if (isset($metas['choices'])) {
                $metas = array_values($metas['choices']);

                $itemValue['question']['metas'] = array_map(function ($choice) use ($self, $container) {
                    return $self->controller->convertAbsoluteUrl($container->get('request'), $choice);
                }, $metas);
            }
        }

        $answer = $question['answer'];
        if (is_array($answer)) {
            $itemValue['question']['answer'] = array_map(function ($answerValue) use ($self, $container) {
                if (is_array($answerValue)) {
                    return implode('|', $answerValue);
                }

                return $self->controller->convertAbsoluteUrl($container->get('request'), $answerValue);
            }, $answer);

            return $itemValue;
        }

        return $itemValue;
    }

    //做过渡使用,移动端course2.0发布后请使用testpaperService中的接口showTestpaperItems
    protected function showTestpaperItems($testId, $resultId = 0)
    {
        $items = $this->getTestpaperService()->findItemsByTestId($testId);
        $questionIds = ArrayToolkit::column($items, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds(ArrayToolkit::column($items, 'questionId'));
        $questions = ArrayToolkit::index($questions, 'id');

        $questions = $this->completeQuestion($items, $questions);

        $itemResults = array();
        if ($resultId) {
            $itemResults = $this->getTestpaperService()->findItemResultsByResultId($resultId);
            $itemResults = ArrayToolkit::index($itemResults, 'questionId');
        }

        $formatItems = array();
        foreach ($items as $questionId => $item) {
            if (array_key_exists($questionId, $itemResults)) {
                $questions[$questionId]['testResult'] = $itemResults[$questionId];
            } elseif ($resultId) {
                //兼容
                $questions[$questionId]['testResult'] = array(
                    'questionId' => (string) $questionId,
                    'status' => 'noAnswer',
                    'score' => '0.0',
                    'answer' => array(),
                );
            }

            $questions[$questionId]['score'] = $item['score'];
            $items[$questionId]['question'] = $questions[$questionId];

            if (0 != $item['parentId']) {
                if (!array_key_exists('items', $items[$item['parentId']])) {
                    $items[$item['parentId']]['items'] = array();
                }

                $items[$item['parentId']]['items'][$questionId] = $items[$questionId];
                $formatItems['material'][$item['parentId']]['items'][$item['seq']] = $items[$questionId];
                unset($items[$questionId]);
            } else {
                $formatItems[$item['questionType']][$item['questionId']] = $items[$questionId];
            }
        }

        return $formatItems;
    }

    protected function completeQuestion($items, $questions)
    {
        foreach ($items as $item) {
            if (!in_array($item['questionId'], ArrayToolkit::column($questions, 'id'))) {
                $questions[$item['questionId']] = array(
                    'id' => $item['questionId'],
                    'isDeleted' => true,
                    'stem' => '此题已删除',
                    'score' => 0,
                    'answer' => '',
                    'type' => $item['questionType'],
                );
            }
        }

        return $questions;
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->controller->getService('Task:TaskService');
    }

    protected function getActivityService()
    {
        return $this->controller->getService('Activity:ActivityService');
    }

    protected function getTestpaperActivityService()
    {
        return $this->controller->getService('Activity:TestpaperActivityService');
    }

    protected function getQuestionService()
    {
        return $this->controller->getService('Question:QuestionService');
    }
}
