<?php

namespace AppBundle\Controller\Testpaper;

use Biz\Activity\Service\ActivityService;
use Biz\Question\Service\CategoryService;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use http\Exception\InvalidArgumentException;
use AppBundle\Common\Paginator;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\TestpaperException;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseSetService;
use Biz\Question\Service\QuestionService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpFoundation\Request;
use Biz\Activity\Service\TestpaperActivityService;

class ManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $sync = $request->query->get('sync');
        if ($courseSet['locked'] && empty($sync)) {
            return $this->redirectToRoute('course_set_manage_sync', array(
                'id' => $id,
                'sideNav' => 'testpaper',
            ));
        }

        return $this->render('testpaper/manage/index.html.twig', array(
            'courseSet' => $courseSet,
        ));
    }

    public function readAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        return $this->forward('AppBundle:Question/QuestionParser:read', array(
            'request' => $request,
            'type' => 'testpaper',
            'courseSet' => $courseSet,
        ));
    }

    public function checkListAction(Request $request, $targetId, $targetType, $type)
    {
        $courseIds = array($targetId);
        $courses = array();
        if ('classroom' === $targetType) {
            $courses = $this->getClassroomService()->findCoursesByClassroomId($targetId);
            $courseIds = ArrayToolkit::column($courses, 'id');
        }

        $conditions = array(
            'courseIds' => empty($courseIds) ? array(-1) : $courseIds,
            'type' => $type,
        );

        $paginator = new Paginator(
            $request,
            $this->getTaskService()->countTasks($conditions),
            10
        );
        $tasks = $this->getTaskService()->searchTasks(
            $conditions,
            array('seq' => 'ASC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        list($tasks, $testpapers) = $this->findTestpapers($tasks, $type);
        $resultStatusNum = $this->findTestpapersStatusNum($tasks, $testpapers);

        return $this->render('testpaper/manage/check-list.html.twig', array(
            'testpapers' => $testpapers,
            'paginator' => $paginator,
            'targetId' => $targetId,
            'targetType' => $targetType,
            'tasks' => $tasks,
            'resultStatusNum' => $resultStatusNum,
            'courses' => ArrayToolkit::index($courses, 'id'),
        ));
    }

    public function checkAction(Request $request, $resultId, $targetId, $source = 'course')
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);

        if (!$result) {
            $this->createNewException(TestpaperException::NOTFOUND_RESULT());
        }
        //还需要是否是教师的权限判断
        if (!$this->getTestpaperService()->canLookTestpaper($result['id'])) {
            return $this->createMessageResponse('error', 'access denied');
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($result['testId']);
        if (!$testpaper) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        if ('reviewing' !== $result['status']) {
            return $this->redirect($this->generateUrl('testpaper_result_show', array('resultId' => $result['id'])));
        }

        if ('POST' === $request->getMethod()) {
            $formData = $request->request->all();
            $isContinue = $formData['isContinue'];
            unset($formData['isContinue']);
            $this->getTestpaperService()->checkFinish($result['id'], $formData);

            $data = array('success' => true, 'goto' => '');
            if ('true' == $isContinue) {
                $route = $this->getRedirectRoute('nextCheck', $source);
                $data['goto'] = $this->generateUrl($route, array('id' => $targetId, 'activityId' => $result['lessonId']));
            }

            return $this->createJsonResponse($data);
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($testpaper['id'], $result['id']);

        $essayQuestions = $this->getCheckedEssayQuestions($questions);

        $student = $this->getUserService()->getUser($result['userId']);
        $accuracy = $this->getTestpaperService()->makeAccuracy($result['id']);
        $total = $this->getTestpaperService()->countQuestionTypes($testpaper, $questions);
        $activity = $this->getActivityService()->getActivity($result['lessonId']);
        $passScore = round($activity['finishData'] * $testpaper['score'], 1);

        return $this->render('testpaper/manage/teacher-check.html.twig', array(
            'paper' => $testpaper,
            'paperResult' => $result,
            'questions' => $essayQuestions,
            'student' => $student,
            'accuracy' => $accuracy,
            'questionTypes' => $this->getTestpaperService()->getCheckedQuestionTypeBySeq($testpaper),
            'total' => $total,
            'source' => $source,
            'targetId' => $targetId,
            'isTeacher' => true,
            'action' => $request->query->get('action', ''),
            'passScore' => $passScore,
        ));
    }

    public function resultListAction(Request $request, $testpaperId, $source, $targetId, $activityId)
    {
        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (!$testpaper) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $status = $request->query->get('status', 'finished');
        $keyword = $request->query->get('keyword', '');

        if (!in_array($status, array('all', 'finished', 'reviewing', 'doing'))) {
            $status = 'all';
        }

        $conditions = array('testId' => $testpaper['id']);
        if ('all' !== $status) {
            $conditions['status'] = $status;
        }
        $conditions['lessonId'] = $activityId;

        if (!empty($keyword)) {
            $searchUser = $this->getUserService()->getUserByNickname($keyword);
            $conditions['userId'] = $searchUser ? $searchUser['id'] : '-1';
        }

        $testpaper['resultStatusNum'] = $this->getTestpaperService()->findPaperResultsStatusNumGroupByStatus($testpaper['id'], $activityId);

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->searchTestpaperResultsCount($conditions),
            10
        );

        $testpaperResults = $this->getTestpaperService()->searchTestpaperResults(
            $conditions,
            array('endTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $studentIds = ArrayToolkit::column($testpaperResults, 'userId');
        $teacherIds = ArrayToolkit::column($testpaperResults, 'checkTeacherId');
        $userIds = array_merge($studentIds, $teacherIds);
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('testpaper/manage/result-list.html.twig', array(
            'testpaper' => $testpaper,
            'status' => $status,
            'paperResults' => $testpaperResults,
            'paginator' => $paginator,
            'users' => $users,
            'source' => $source,
            'targetId' => $targetId,
            'isTeacher' => true,
            'keyword' => $keyword,
        ));
    }

    public function buildCheckAction(Request $request, $courseSetId, $type)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $data = $request->request->all();
        $data['courseSetId'] = $courseSet['id'];

        $result = $this->getTestpaperService()->canBuildTestpaper($type, $data);

        return $this->createJsonResponse($result);
    }

    public function infoAction(Request $request, $id)
    {
        $this->getCourseSetService()->tryManageCourseSet($id);

        $testpaperId = $request->request->get('testpaperId');

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (!$this->getQuestionBankService()->canManageBank($testpaper['bankId'])) {
            return $this->createMessageResponse('error', 'can not manage bank');
        }

        if (empty($testpaper)) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        $items = $this->getTestpaperService()->getItemsCountByParams(
            array('testId' => $testpaperId, 'parentIdDefault' => 0),
            'questionType'
        );
        $subItems = $this->getTestpaperService()->getItemsCountByParams(
            array('testId' => $testpaperId, 'parentId' => 0)
        );

        $items = ArrayToolkit::index($items, 'questionType');

        $items['material'] = $subItems[0];

        return $this->render('testpaper/manage/item-get-table.html.twig', array(
            'items' => $items,
        ));
    }

    public function resultAnalysisAction(Request $request, $targetId, $targetType, $activityId, $studentNum)
    {
        $activity = $this->getActivityService()->getActivity($activityId);

        if (empty($activity) || 'testpaper' != $activity['mediaType']) {
            return $this->createMessageResponse('error', 'Argument invalid');
        }

        $analyses = $this->getQuestionAnalysisService()->searchAnalysis(array('activityId' => $activity['id']), array(), 0, PHP_INT_MAX);

        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        $paper = $this->getTestpaperService()->getTestpaper($testpaperActivity['mediaId']);
        if (empty($paper)) {
            return $this->createMessageResponse('info', 'Paper not found');
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($paper['id']);

        $relatedData = $this->findRelatedData($activity, $paper);
        $relatedData['studentNum'] = $studentNum;

        return $this->render('testpaper/manage/result-analysis.html.twig', array(
            'analyses' => ArrayToolkit::groupIndex($analyses, 'questionId', 'choiceIndex'),
            'paper' => $paper,
            'questions' => $questions,
            'questionTypes' => $this->getTestpaperService()->getCheckedQuestionTypeBySeq($paper),
            'relatedData' => $relatedData,
            'targetType' => $targetType,
        ));
    }

    public function resultGraphAction($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);

        if (!$activity || 'testpaper' != $activity['mediaType']) {
            return $this->createMessageResponse('error', 'Argument Invalid');
        }

        $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperActivity['mediaId']);
        $userFirstResults = $this->getTestpaperService()->findResultsByTestIdAndActivityId($testpaper['id'], $activity['id']);

        $data = $this->fillGraphData($testpaper, $userFirstResults);
        $analysis = $this->analysisFirstResults($userFirstResults);

        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);

        return $this->render('testpaper/manage/result-graph-modal.html.twig', array(
            'activity' => $activity,
            'testpaper' => $testpaper,
            'data' => $data,
            'analysis' => $analysis,
            'task' => $task,
        ));
    }

    public function reEditAction(Request $request, $token)
    {
        return $this->forward('AppBundle:Question/QuestionParser:reEdit', array(
            'request' => $request,
            'token' => $token,
            'type' => 'testpaper',
        ));
    }

    public function editTemplateAction(Request $request, $type)
    {
        $question = $request->request->get('question', array());
        $seq = $request->request->get('seq', 1);
        $token = $request->request->get('token', '');
        $isSub = $request->request->get('isSub', '0');
        $isTestpaper = $request->request->get('isTestpaper', 1);
        $method = $request->request->get('method', 'edit');
        $questionBankId = $request->request->get('questionBankId', 0);

        $question = $this->filterQuestion($question);

        return $this->render("testpaper/subject/type/{$type}.html.twig", array(
            'question' => $question,
            'seq' => $seq,
            'token' => $token,
            'type' => $type,
            'isSub' => $isSub,
            'isTestpaper' => $isTestpaper,
            'method' => $method,
            'questionBankId' => $questionBankId,
        ));
    }

    public function convertTemplateAction(Request $request)
    {
        $data = $request->request->all();
        $fromType = $data['fromType'];
        $toType = $data['toType'];
        if (empty($data['question'])) {
            throw new InvalidArgumentException('缺少必要参数');
        }
        if (in_array($fromType, array('choice', 'single_choice', 'uncertain_choice')) && in_array($toType, array('choice', 'single_choice', 'uncertain_choice'))) {
            $question = $this->convertChoice($toType, $data['question']);
        }

        if (in_array($fromType, array('essay')) && in_array($toType, array('single_choice', 'choice', 'uncertain_choice', 'fill', 'determine'))) {
            $question = $this->convertEssay($toType, $data['question']);
        }

        return $this->render("testpaper/subject/type/{$toType}.html.twig", array(
            'question' => $question,
            'seq' => $data['seq'],
            'token' => $data['token'],
            'type' => $toType,
            'isSub' => $data['isSub'],
            'isTestpaper' => $data['isTestpaper'],
            'method' => empty($data['method']) ? 'edit' : $data['method'],
            'questionBankId' => empty($data['questionBankId']) ? 0 : $data['questionBankId'],
        ));
    }

    protected function convertEssay($toType, $question)
    {
        $question['type'] = $toType;
        if (in_array($toType, array('choice', 'single_choice', 'uncertain_choice'))) {
            $question['options'] = array();
            $question['answers'] = array();
        }
        $question = $this->filterQuestion($question);

        return $question;
    }

    protected function convertChoice($toType, $question)
    {
        $question['type'] = $toType;
        $question['options'] = empty($question['options']) ? array() : $question['options'];
        $question['answers'] = isset($question['right']) ? (is_array($question['right']) ? $question['right'] : array($question['right'])) : array();
        if ('single_choice' == $toType) {
            $question['answers'] = array(reset($question['answers']));
        }
        $question = $this->filterQuestion($question);

        return $question;
    }

    public function showTemplateAction(Request $request, $type)
    {
        $question = $request->request->get('question', array());
        $seq = $request->request->get('seq', 1);
        $token = $request->request->get('token', '');
        $isSub = $request->request->get('isSub', '0');

        $question = $this->filterQuestion($question);
        $question = $this->wrapQuestion($question);

        if ('fill' == $type) {
            $question['stemShow'] = preg_replace('/^((\d{0,5}(\.|、|。|\s))|((\(|（)\d{0,5}(\)|）)))/', '', $question['stem']);
            $question['stemShow'] = preg_replace('/(\[\[(.+?)\]\])/is', '_____', $question['stem']);
        }

        if (!empty($isSub)) {
            return $this->render("testpaper/subject/item/show/sub-{$type}.html.twig", array(
                'item' => $question,
                'seq' => $seq,
                'token' => $token,
                'type' => $type,
            ));
        }

        return $this->render("testpaper/subject/item/show/{$type}.html.twig", array(
            'item' => $question,
            'seq' => $seq,
            'token' => $token,
            'type' => $type,
        ));
    }

    public function saveImportTestpaperAction(Request $request, $token)
    {
        $content = $request->getContent();
        $testpaper = json_decode($content, true);
        $token = $this->getTokenService()->verifyToken('upload.course_private_file', $token);
        $data = $token['data'];
        if (!$this->getQuestionBankService()->canManageBank($data['questionBankId'])) {
            $this->createNewException(QuestionBankException::FORBIDDEN_ACCESS_BANK());
        }
        $this->getTestpaperService()->importTestpaper($testpaper, $token);

        return $this->createJsonResponse(true);
    }

    public function optionTemplateAction(Request $request, $type)
    {
        $field = $request->query->all();

        return $this->render('testpaper/subject/option.html.twig', array(
            'type' => $type,
            'order' => $field['order'],
        ));
    }

    protected function wrapQuestion($question)
    {
        if (!empty($question['categoryId'])) {
            $category = $this->getQuestionCategoryService()->getCategory($question['categoryId']);
            $question['category'] = empty($category) ? array() : $category;
        }

        return $question;
    }

    protected function filterQuestion($question)
    {
        return ArrayToolkit::parts($question, array(
            'stem',
            'stemShow',
            'type',
            'options',
            'answer',
            'answers',
            'score',
            'missScore',
            'analysis',
            'attachments',
            'attachment',
            'subQuestions',
            'difficulty',
            'categoryId',
        ));
    }

    protected function fillGraphData($testpaper, $userFirstResults)
    {
        $data = array('xScore' => array(), 'yFirstNum' => array(), 'yMaxNum' => array());

        $totalScore = $testpaper['score'];
        $maxTmpScore = 0;

        $column = $totalScore <= 5 ? ($totalScore / 1) : 5;
        for ($i = 1; $i <= $column; ++$i) {
            $maxScoreCount = 0;
            $firstScoreCount = 0;
            $minTmpScore = $maxTmpScore;
            $maxTmpScore = $totalScore * ($i / $column);

            foreach ($userFirstResults as $result) {
                if ($maxTmpScore == $totalScore) {
                    if ($result['firstScore'] >= $minTmpScore && $result['firstScore'] <= $maxTmpScore) {
                        ++$firstScoreCount;
                    }

                    if ($result['maxScore'] >= $minTmpScore && $result['maxScore'] <= $maxTmpScore) {
                        ++$maxScoreCount;
                    }
                } else {
                    if ($result['firstScore'] >= $minTmpScore && $result['firstScore'] < $maxTmpScore) {
                        ++$firstScoreCount;
                    }

                    if ($result['maxScore'] >= $minTmpScore && $result['maxScore'] < $maxTmpScore) {
                        ++$maxScoreCount;
                    }
                }
            }

            $data['xScore'][] = $minTmpScore.'-'.$maxTmpScore;
            $data['yFirstNum'][] = $firstScoreCount;
            $data['yMaxNum'][] = $maxScoreCount;
        }

        return json_encode($data);
    }

    protected function analysisFirstResults($userFirstResults)
    {
        if (empty($userFirstResults)) {
            return array();
        }

        $data = array();
        $scores = ArrayToolkit::column($userFirstResults, 'firstScore');
        $data['avg'] = round(array_sum($scores) / count($userFirstResults), 1);
        $data['maxScore'] = max($scores);

        $count = 0;
        foreach ($userFirstResults as $result) {
            if ('unpassed' != $result['firstPassedStatus']) {
                ++$count;
            }
        }

        $data['passPercent'] = round($count / count($userFirstResults) * 100, 1);

        return $data;
    }

    protected function getCheckedEssayQuestions($questions)
    {
        $essayQuestions = array();

        $essayQuestions['essay'] = !empty($questions['essay']) ? $questions['essay'] : array();

        if (empty($questions['material'])) {
            return $essayQuestions;
        }

        foreach ($questions['material'] as $questionId => $question) {
            $questionTypes = ArrayToolkit::column(empty($question['subs']) ? array() : $question['subs'], 'type');

            if (in_array('essay', $questionTypes)) {
                $essayQuestions['material'][$questionId] = $question;
            }
        }

        return $essayQuestions;
    }

    protected function findRelatedData($activity, $paper)
    {
        $relatedData = array();
        $userFirstResults = $this->getTestpaperService()->findExamFirstResults($paper['id'], $paper['type'], $activity['id']);

        $relatedData['total'] = count($userFirstResults);

        $userFirstResults = ArrayToolkit::group($userFirstResults, 'status');
        $finishedResults = empty($userFirstResults['finished']) ? array() : $userFirstResults['finished'];

        $relatedData['finished'] = count($finishedResults);
        $scores = array_sum(ArrayToolkit::column($finishedResults, 'score'));
        $avg = empty($relatedData['finished']) ? 0 : $scores / $relatedData['finished'];
        $relatedData['avgScore'] = number_format($avg, 1);

        return $relatedData;
    }

    protected function findTestpapers($tasks, $type)
    {
        if (empty($tasks)) {
            return array($tasks, array());
        }

        $activityIds = ArrayToolkit::column($tasks, 'activityId');
        $activities = $this->getActivityService()->findActivities($activityIds);
        $activities = ArrayToolkit::index($activities, 'id');

        if ('testpaper' == $type) {
            $testpaperActivityIds = ArrayToolkit::column($activities, 'mediaId');
            $testpaperActivities = $this->getTestpaperActivityService()->findActivitiesByIds($testpaperActivityIds);
            $testpaperActivities = ArrayToolkit::index($testpaperActivities, 'id');
            $ids = ArrayToolkit::column($testpaperActivities, 'mediaId');

            array_walk($tasks, function (&$task, $key) use ($activities, $testpaperActivities) {
                $activity = $activities[$task['activityId']];
                $task['testId'] = $testpaperActivities[$activity['mediaId']]['mediaId'];
            });
        } else {
            $ids = ArrayToolkit::column($activities, 'mediaId');
            array_walk($tasks, function (&$task, $key) use ($activities) {
                $activity = $activities[$task['activityId']];
                $task['testId'] = $activity['mediaId'];
            });
        }

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($ids);

        if (empty($testpapers)) {
            return array($activities, array());
        }

        return array($tasks, $testpapers);
    }

    protected function findTestpapersStatusNum($tasks, $testpapers)
    {
        $resultStatusNum = array();
        foreach ($tasks as $task) {
            if (empty($testpapers[$task['testId']])) {
                continue;
            }

            $resultStatusNum[$task['activityId']] = $this->getTestpaperService()->findPaperResultsStatusNumGroupByStatus($task['testId'], $task['activityId']);
        }

        return $resultStatusNum;
    }

    protected function getRedirectRoute($mode, $type)
    {
        $routes = array(
            'nextCheck' => array(
                'course' => 'course_manage_exam_next_result_check',
                'classroom' => 'classroom_manage_exam_next_result_check',
            ),
        );

        return $routes[$mode][$type];
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return QuestionService
     */
    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    /**
     * @return CategoryService
     */
    protected function getQuestionCategoryService()
    {
        return $this->createService('Question:CategoryService');
    }

    protected function getQuestionAnalysisService()
    {
        return $this->createService('Question:QuestionAnalysisService');
    }

    /**
     * @return TaskService
     */
    public function getTaskService()
    {
        return $this->createService('Task:TaskService');
    }

    /**
     * @return ActivityService
     */
    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    /**
     * @return TestpaperActivityService
     */
    protected function getTestpaperActivityService()
    {
        return $this->createService('Activity:TestpaperActivityService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }

    /**
     * @return QuestionBankService
     */
    protected function getQuestionBankService()
    {
        return $this->createService('QuestionBank:QuestionBankService');
    }
}
