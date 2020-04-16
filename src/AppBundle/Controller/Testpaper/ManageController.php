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
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use Biz\Common\CommonException;
use Biz\Activity\Service\HomeworkActivityService;

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
        $courseSets = array();
        if ('classroom' === $targetType) {
            $courses = $this->getClassroomService()->findCoursesByClassroomId($targetId);
            $courseIds = ArrayToolkit::column($courses, 'id');
            $courseSets = $this->getCourseSetService()->findCourseSetsByCourseIds($courseIds);
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

        $resultStatusNum = $this->findTestpapersStatusNum($tasks);

        return $this->render('testpaper/manage/check-list.html.twig', array(
            'testpapers' => $testpapers,
            'paginator' => $paginator,
            'targetId' => $targetId,
            'targetType' => $targetType,
            'tasks' => $tasks,
            'resultStatusNum' => $resultStatusNum,
            'courses' => ArrayToolkit::index($courses, 'id'),
            'courseSets' => ArrayToolkit::index($courseSets, 'id'),
        ));
    }

    public function checkAction(Request $request, $answerRecordId, $targetId, $source = 'course')
    {
        switch ($source) {
            case 'course':
                $this->getCourseService()->tryManageCourse($targetId);
                break;
            case 'classroom':
                $this->getClassroomService()->tryHandleClassroom($targetId);
                break;
            default:
                $this->createNewException(CommonException::ERROR_PARAMETER());
                break;
        }

        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        if (!$answerRecord) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        if ('reviewing' !== $answerRecord['status']) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        return $this->forward('AppBundle:AnswerEngine/AnswerEngine:reviewAnswer', array(
            'answerRecordId' => $answerRecordId,
        ));
    }

    public function resultListAction(Request $request, $testpaperId, $source, $targetId, $activityId)
    {
        $assessment = $this->getAssessmentService()->getAssessment($testpaperId);
        if (!$assessment) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $activity = $this->getActivityService()->getActivity($activityId);
        $answerScene = $this->getAnswerSceneByActivityId($activityId);
        $status = $request->query->get('status', 'finished');
        $keyword = $request->query->get('keyword', '');

        if (!in_array($status, array('all', 'finished', 'reviewing', 'doing'))) {
            $status = 'all';
        }

        $conditions = array('answer_scene_id' => $answerScene['id']);
        if ('all' !== $status) {
            $conditions['status'] = $status;
        }

        if (!empty($keyword)) {
            $searchUser = $this->getUserService()->getUserByNickname($keyword);
            $conditions['user_id'] = $searchUser ? $searchUser['id'] : '-1';
        }

        $paginator = new Paginator(
            $request,
            $this->getAnswerRecordService()->count($conditions),
            10
        );

        $answerRecords = $this->getAnswerRecordService()->search(
            $conditions,
            array(),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $answerReports = ArrayToolkit::index($this->getAnswerReportService()->findByIds(ArrayToolkit::column($answerRecords, 'answer_report_id')), 'id');
        $studentIds = ArrayToolkit::column($answerRecords, 'user_id');
        $teacherIds = ArrayToolkit::column($answerReports, 'review_user_id');
        $userIds = array_merge($studentIds, $teacherIds);
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('testpaper/manage/result-list.html.twig', array(
            'assessment' => $assessment,
            'activity' => $activity,
            'status' => $status,
            'answerRecords' => $answerRecords,
            'answerReports' => $answerReports,
            'answerScene' => $answerScene,
            'paginator' => $paginator,
            'users' => $users,
            'source' => $source,
            'targetId' => $targetId,
            'isTeacher' => true,
            'keyword' => $keyword,
        ));
    }

    protected function getAnswerSceneByActivityId($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);
        if ('testpaper' == $activity['mediaType']) {
            $testpaperActivity = $this->getTestpaperActivityService()->getActivity($activity['mediaId']);

            return $this->getAnswerSceneService()->get($testpaperActivity['answerSceneId']);
        }

        if ('homework' == $activity['mediaType']) {
            $homeworkActivity = $this->getHomeworkActivityService()->get($activity['mediaId']);

            return $this->getAnswerSceneService()->get($homeworkActivity['answerSceneId']);
        }
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

        $assessmentId = $request->request->get('testpaperId');

        $assessment = $this->getAssessmentService()->getAssessment($assessmentId);

        $bank = $this->getQuestionBankService()->getQuestionBank($assessment['bank_id']);
        if (empty($bank)) {
            return $this->createMessageResponse('error', 'bank is not ex33ist');
        }

        if (empty($assessment)) {
            return $this->createMessageResponse('error', 'assessment not found');
        }

        $assessment = $this->getAssessmentService()->showAssessment($assessmentId);

        return $this->render('testpaper/manage/item-get-table.html.twig', array(
            'assessment' => $assessment,
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
        $postData = json_decode($content, true);
        $token = $this->getTokenService()->verifyToken('upload.course_private_file', $token);
        $data = $token['data'];
        if (!$this->getQuestionBankService()->canManageBank($data['questionBankId'])) {
            $this->createNewException(QuestionBankException::FORBIDDEN_ACCESS_BANK());
        }
        $questionBank = $this->getQuestionBankService()->getQuestionBank($data['questionBankId']);
        $items = $postData['items'];
        $assessment = array(
            'name' => $postData['fileName'],
            'bank_id' => $questionBank['itemBankId'],
            'displayable' => 1,
            'sections' => $this->getSections($items),
        );
        $this->getAssessmentService()->importAssessment($assessment);

        return $this->createJsonResponse(array('goto' => $this->generateUrl('question_bank_manage_testpaper_list', array('id' => $questionBank['id']))));
    }

    protected function getSections($items)
    {
        $typeGroupItems = ArrayToolkit::group($items, 'type');
        $sections = array();
        $questionTypes = $this->getQuestionTypes();
        foreach ($questionTypes as $type => $config) {
            if (!empty($typeGroupItems[$type])) {
                $sections[] = array(
                    'name' => $this->trans($config['name']),
                    'items' => $typeGroupItems[$type],
                );
            }
        }

        return $sections;
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
                $task['answerSceneId'] = $testpaperActivities[$activity['mediaId']]['answerSceneId'];
            });
        } else {
            $homeworkActivityIds = ArrayToolkit::column($activities, 'mediaId');
            $homeworkActivities = $this->getHomeworkActivityService()->findByIds($homeworkActivityIds);
            $homeworkActivities = ArrayToolkit::index($homeworkActivities, 'id');
            $ids = ArrayToolkit::column($homeworkActivities, 'assessmentId');

            array_walk($tasks, function (&$task, $key) use ($activities, $homeworkActivities) {
                $activity = $activities[$task['activityId']];
                $task['testId'] = $homeworkActivities[$activity['mediaId']]['assessmentId'];
                $task['answerSceneId'] = $homeworkActivities[$activity['mediaId']]['answerSceneId'];
            });
        }

        $testpapers = $this->getAssessmentService()->findAssessmentsByIds($ids);

        if (empty($testpapers)) {
            return array($activities, array());
        }

        return array($tasks, $testpapers);
    }

    protected function findTestpapersStatusNum($tasks)
    {
        $resultStatusNum = array();
        foreach ($tasks as $task) {
            if (empty($task['answerSceneId'])) {
                continue;
            }

            $answerRecords = $this->getAnswerRecordService()->search(
                array('answer_scene_id' => $task['answerSceneId']),
                array(),
                0,
                $this->getAnswerRecordService()->count(array('answer_scene_id' => $task['answerSceneId']))
            );
            $resultStatusNum[$task['activityId']] = ArrayToolkit::group($answerRecords, 'status');
            foreach ($resultStatusNum[$task['activityId']] as &$status) {
                $status = count($status);
            }
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

    protected function getQuestionTypes()
    {
        return $this->get('extension.manager')->getQuestionTypes();
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
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

    /**
     * @return AssessmentService
     */
    protected function getAssessmentService()
    {
        return $this->createService('ItemBank:Assessment:AssessmentService');
    }

    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }

    protected function getAnswerSceneService()
    {
        return $this->createService('ItemBank:Answer:AnswerSceneService');
    }

    protected function getAnswerReportService()
    {
        return $this->createService('ItemBank:Answer:AnswerReportService');
    }

    /**
     * @return HomeworkActivityService
     */
    protected function getHomeworkActivityService()
    {
        return $this->getBiz()->service('Activity:HomeworkActivityService');
    }
}
