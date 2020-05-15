<?php

namespace AppBundle\Controller\Testpaper;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\BaseController;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseSetService;
use Biz\Question\Service\CategoryService;
use Biz\Question\Service\QuestionService;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\Service\TestpaperService;
use Biz\Testpaper\TestpaperException;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use http\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;

class ManageController extends BaseController
{
    public function indexAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        $sync = $request->query->get('sync');
        if ($courseSet['locked'] && empty($sync)) {
            return $this->redirectToRoute('course_set_manage_sync', [
                'id' => $id,
                'sideNav' => 'testpaper',
            ]);
        }

        return $this->render('testpaper/manage/index.html.twig', [
            'courseSet' => $courseSet,
        ]);
    }

    public function readAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        return $this->forward('AppBundle:Question/QuestionParser:read', [
            'request' => $request,
            'type' => 'testpaper',
            'courseSet' => $courseSet,
        ]);
    }

    public function checkListAction(Request $request, $targetId, $targetType, $type)
    {
        $courseIds = [$targetId];
        $courses = [];
        $courseSets = [];
        if ('classroom' === $targetType) {
            $courses = $this->getClassroomService()->findCoursesByClassroomId($targetId);
            $courseIds = ArrayToolkit::column($courses, 'id');
            $courseSets = $this->getCourseSetService()->findCourseSetsByCourseIds($courseIds);
        }

        $conditions = [
            'courseIds' => empty($courseIds) ? [-1] : $courseIds,
            'type' => $type,
        ];

        $paginator = new Paginator(
            $request,
            $this->getTaskService()->countTasks($conditions),
            10
        );
        $tasks = $this->getTaskService()->searchTasks(
            $conditions,
            ['seq' => 'ASC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        list($tasks, $testpapers) = $this->findTestpapers($tasks, $type);

        $resultStatusNum = $this->findTestpapersStatusNum($tasks);

        return $this->render('testpaper/manage/check-list.html.twig', [
            'testpapers' => $testpapers,
            'paginator' => $paginator,
            'targetId' => $targetId,
            'targetType' => $targetType,
            'tasks' => $tasks,
            'resultStatusNum' => $resultStatusNum,
            'courses' => ArrayToolkit::index($courses, 'id'),
            'courseSets' => ArrayToolkit::index($courseSets, 'id'),
        ]);
    }

    public function checkAction(Request $request, $answerRecordId, $targetId, $source = 'course')
    {
        $answerRecord = $this->getAnswerRecordService()->get($answerRecordId);
        if (!$answerRecord) {
            $this->createNewException(TestpaperException::NOTFOUND_RESULT());
        }

        switch ($source) {
            case 'course':
                $successContinueGotoUrl = $this->generateUrl('course_manage_exam_next_result_check', ['id' => $targetId, 'activityId' => $this->getActivityIdByAnswerSceneId($answerRecord['answer_scene_id'])]);
                $this->getCourseService()->tryManageCourse($targetId);
                break;
            case 'classroom':
                $successContinueGotoUrl = $this->generateUrl('classroom_manage_exam_next_result_check', ['id' => $targetId, 'activityId' => $this->getActivityIdByAnswerSceneId($answerRecord['answer_scene_id'])]);
                $this->getClassroomService()->tryHandleClassroom($targetId);
                break;
            default:
                $this->createNewException(CommonException::ERROR_PARAMETER());
                break;
        }

        return $this->forward('AppBundle:AnswerEngine/AnswerEngine:reviewAnswer', [
            'answerRecordId' => $answerRecordId,
            'successGotoUrl' => $this->generateUrl('testpaper_result_show', ['action' => 'check', 'answerRecordId' => $answerRecordId]),
            'successContinueGotoUrl' => $successContinueGotoUrl,
        ]);
    }

    protected function getActivityIdByAnswerSceneId($answerSceneId)
    {
        $testpaperActivity = $this->getTestpaperActivityService()->getActivityByAnswerSceneId($answerSceneId);

        return $this->getActivityService()->getByMediaIdAndMediaType($testpaperActivity['id'], 'testpaper')['id'];
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

        if (!in_array($status, ['all', 'finished', 'reviewing', 'doing'])) {
            $status = 'all';
        }

        $conditions = ['answer_scene_id' => $answerScene['id']];
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
            [],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $answerReports = ArrayToolkit::index($this->getAnswerReportService()->findByIds(ArrayToolkit::column($answerRecords, 'answer_report_id')), 'id');
        $studentIds = ArrayToolkit::column($answerRecords, 'user_id');
        $teacherIds = ArrayToolkit::column($answerReports, 'review_user_id');
        $userIds = array_merge($studentIds, $teacherIds);
        $users = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('testpaper/manage/result-list.html.twig', [
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
        ]);
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

        return $this->render('testpaper/manage/item-get-table.html.twig', [
            'assessment' => $assessment,
        ]);
    }

    public function resultAnalysisAction(Request $request, $targetId, $targetType, $activityId, $studentNum)
    {
        $activity = $this->getActivityService()->getActivity($activityId, true);
        if (empty($activity) || 'testpaper' != $activity['mediaType']) {
            return $this->createMessageResponse('error', 'Argument invalid');
        }

        if (empty($activity['ext']['testpaper'])) {
            return $this->createMessageResponse('info', 'Paper not found');
        }

        $answerSceneReport = $this->getAnswerSceneService()->getAnswerSceneReport($activity['ext']['answerScene']['id']);
        if (empty($answerSceneReport['question_reports'])) {
            $this->getAnswerSceneService()->buildAnswerSceneReport($activity['ext']['answerScene']['id']);
            $answerSceneReport = $this->getAnswerSceneService()->getAnswerSceneReport($activity['ext']['answerScene']['id']);
        }

        return $this->render('testpaper/manage/result-analysis.html.twig', [
            'activity' => $activity,
            'studentNum' => $studentNum,
            'answerSceneReport' => $answerSceneReport,
            'assessment' => $activity['ext']['testpaper'],
            'targetType' => $targetType,
        ]);
    }

    public function resultGraphAction($activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId, true);

        if (!$activity || 'testpaper' != $activity['mediaType']) {
            return $this->createMessageResponse('error', 'Argument Invalid');
        }

        $answerRecords = $this->getAnswerRecordsByAnswerSceneId($activity['ext']['answerSceneId']);
        $firstAndMaxScore = $this->getCalculateRecordsFirstAndMaxScore($answerRecords, $activity);
        $data = $this->fillGraphData($firstAndMaxScore, $activity);
        $analysis = $this->analysisFirstResults($firstAndMaxScore);
        $task = $this->getTaskService()->getTaskByCourseIdAndActivityId($activity['fromCourseId'], $activity['id']);

        return $this->render('testpaper/manage/result-graph-modal.html.twig', [
            'activity' => $activity,
            'data' => $data,
            'analysis' => $analysis,
            'task' => $task,
        ]);
    }

    protected function getAnswerRecordsByAnswerSceneId($answerSceneId)
    {
        $conditions = [
            'status' => 'finished',
            'answer_scene_id' => $answerSceneId,
        ];
        $answerRecords = $this->getAnswerRecordService()->search($conditions, [], 0, $this->getAnswerRecordService()->count($conditions));
        $answerReports = ArrayToolkit::index(
            $this->getAnswerReportService()->findByIds(ArrayToolkit::column($answerRecords, 'answer_report_id')),
            'id'
        );
        foreach ($answerRecords as &$answerRecord) {
            $answerRecord['score'] = $answerReports[$answerRecord['answer_report_id']]['score'];
        }

        $answerRecords = ArrayToolkit::group($answerRecords, 'user_id');

        return $answerRecords;
    }

    protected function getCalculateRecordsFirstAndMaxScore($answerRecords, $activity)
    {
        $data = [];
        foreach ($answerRecords as $userAnswerRecords) {
            $firstScore = $userAnswerRecords[0]['score'];
            $maxScore = $this->getUserMaxScore($userAnswerRecords);
            $data[] = [
                'firstScore' => $firstScore,
                'maxScore' => $maxScore,
                'firstPassedStatus' => $firstScore >= $activity['ext']['answerScene']['pass_score'] ? 'passed' : 'unpassed',
                'maxPassedStatus' => $maxScore >= $activity['ext']['answerScene']['pass_score'] ? 'passed' : 'unpassed',
            ];
        }

        return $data;
    }

    protected function getUserMaxScore($userAnswerRecords)
    {
        if (1 == count($userAnswerRecords)) {
            return $userAnswerRecords[0]['score'];
        }

        $scores = ArrayToolkit::column($userAnswerRecords, 'score');

        return max($scores);
    }

    public function reEditAction(Request $request, $token)
    {
        return $this->forward('AppBundle:Question/QuestionParser:reEdit', [
            'request' => $request,
            'token' => $token,
            'type' => 'testpaper',
        ]);
    }

    public function editTemplateAction(Request $request, $type)
    {
        $question = $request->request->get('question', []);
        $seq = $request->request->get('seq', 1);
        $token = $request->request->get('token', '');
        $isSub = $request->request->get('isSub', '0');
        $isTestpaper = $request->request->get('isTestpaper', 1);
        $method = $request->request->get('method', 'edit');
        $questionBankId = $request->request->get('questionBankId', 0);

        $question = $this->filterQuestion($question);

        return $this->render("testpaper/subject/type/{$type}.html.twig", [
            'question' => $question,
            'seq' => $seq,
            'token' => $token,
            'type' => $type,
            'isSub' => $isSub,
            'isTestpaper' => $isTestpaper,
            'method' => $method,
            'questionBankId' => $questionBankId,
        ]);
    }

    public function convertTemplateAction(Request $request)
    {
        $data = $request->request->all();
        $fromType = $data['fromType'];
        $toType = $data['toType'];
        if (empty($data['question'])) {
            throw new InvalidArgumentException('缺少必要参数');
        }
        if (in_array($fromType, ['choice', 'single_choice', 'uncertain_choice']) && in_array($toType, ['choice', 'single_choice', 'uncertain_choice'])) {
            $question = $this->convertChoice($toType, $data['question']);
        }

        if (in_array($fromType, ['essay']) && in_array($toType, ['single_choice', 'choice', 'uncertain_choice', 'fill', 'determine'])) {
            $question = $this->convertEssay($toType, $data['question']);
        }

        return $this->render("testpaper/subject/type/{$toType}.html.twig", [
            'question' => $question,
            'seq' => $data['seq'],
            'token' => $data['token'],
            'type' => $toType,
            'isSub' => $data['isSub'],
            'isTestpaper' => $data['isTestpaper'],
            'method' => empty($data['method']) ? 'edit' : $data['method'],
            'questionBankId' => empty($data['questionBankId']) ? 0 : $data['questionBankId'],
        ]);
    }

    protected function convertEssay($toType, $question)
    {
        $question['type'] = $toType;
        if (in_array($toType, ['choice', 'single_choice', 'uncertain_choice'])) {
            $question['options'] = [];
            $question['answers'] = [];
        }
        $question = $this->filterQuestion($question);

        return $question;
    }

    protected function convertChoice($toType, $question)
    {
        $question['type'] = $toType;
        $question['options'] = empty($question['options']) ? [] : $question['options'];
        $question['answers'] = isset($question['right']) ? (is_array($question['right']) ? $question['right'] : [$question['right']]) : [];
        if ('single_choice' == $toType) {
            $question['answers'] = [reset($question['answers'])];
        }
        $question = $this->filterQuestion($question);

        return $question;
    }

    public function showTemplateAction(Request $request, $type)
    {
        $question = $request->request->get('question', []);
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
            return $this->render("testpaper/subject/item/show/sub-{$type}.html.twig", [
                'item' => $question,
                'seq' => $seq,
                'token' => $token,
                'type' => $type,
            ]);
        }

        return $this->render("testpaper/subject/item/show/{$type}.html.twig", [
            'item' => $question,
            'seq' => $seq,
            'token' => $token,
            'type' => $type,
        ]);
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
        $assessment = [
            'name' => $postData['fileName'],
            'bank_id' => $questionBank['itemBankId'],
            'displayable' => 1,
            'sections' => $this->assembleSections($items),
        ];
        $this->getAssessmentService()->importAssessment($assessment);

        return $this->createJsonResponse(['goto' => $this->generateUrl('question_bank_manage_testpaper_list', ['id' => $questionBank['id']])]);
    }

    protected function assembleSections($items)
    {
        $typeGroupItems = ArrayToolkit::group($items, 'type');
        $sections = [];
        $questionTypes = $this->getQuestionTypes();
        foreach ($questionTypes as $type => $config) {
            if (!empty($typeGroupItems[$type])) {
                $sections[] = [
                    'name' => $this->trans($config['name']),
                    'items' => $typeGroupItems[$type],
                ];
            }
        }

        return $sections;
    }

    public function optionTemplateAction(Request $request, $type)
    {
        $field = $request->query->all();

        return $this->render('testpaper/subject/option.html.twig', [
            'type' => $type,
            'order' => $field['order'],
        ]);
    }

    protected function wrapQuestion($question)
    {
        if (!empty($question['categoryId'])) {
            $category = $this->getQuestionCategoryService()->getCategory($question['categoryId']);
            $question['category'] = empty($category) ? [] : $category;
        }

        return $question;
    }

    protected function filterQuestion($question)
    {
        return ArrayToolkit::parts($question, [
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
        ]);
    }

    protected function fillGraphData($firstAndMaxScore, $activity)
    {
        $data = ['xScore' => [], 'yFirstNum' => [], 'yMaxNum' => []];
        $totalScore = $activity['ext']['testpaper']['total_score'];
        $maxTmpScore = 0;

        $column = $totalScore <= 5 ? ($totalScore / 1) : 5;
        for ($i = 1; $i <= $column; ++$i) {
            $maxScoreCount = 0;
            $firstScoreCount = 0;
            $minTmpScore = $maxTmpScore;
            $maxTmpScore = $totalScore * ($i / $column);

            foreach ($firstAndMaxScore as $result) {
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

    protected function analysisFirstResults($firstAndMaxScore)
    {
        if (empty($firstAndMaxScore)) {
            return [];
        }

        $data = [];
        $scores = ArrayToolkit::column($firstAndMaxScore, 'firstScore');
        $data['avg'] = sprintf('%.1f', array_sum($scores) / count($firstAndMaxScore));
        $data['maxScore'] = max($scores);

        $count = 0;
        foreach ($firstAndMaxScore as $result) {
            if ('unpassed' != $result['firstPassedStatus']) {
                ++$count;
            }
        }

        $data['passPercent'] = round($count / count($firstAndMaxScore) * 100, 1);

        return $data;
    }

    protected function getCheckedEssayQuestions($questions)
    {
        $essayQuestions = [];

        $essayQuestions['essay'] = !empty($questions['essay']) ? $questions['essay'] : [];

        if (empty($questions['material'])) {
            return $essayQuestions;
        }

        foreach ($questions['material'] as $questionId => $question) {
            $questionTypes = ArrayToolkit::column(empty($question['subs']) ? [] : $question['subs'], 'type');

            if (in_array('essay', $questionTypes)) {
                $essayQuestions['material'][$questionId] = $question;
            }
        }

        return $essayQuestions;
    }

    protected function findRelatedData($activity, $paper)
    {
        $relatedData = [];
        $userFirstResults = $this->getTestpaperService()->findExamFirstResults($paper['id'], $paper['type'], $activity['id']);

        $relatedData['total'] = count($userFirstResults);

        $userFirstResults = ArrayToolkit::group($userFirstResults, 'status');
        $finishedResults = empty($userFirstResults['finished']) ? [] : $userFirstResults['finished'];

        $relatedData['finished'] = count($finishedResults);
        $scores = array_sum(ArrayToolkit::column($finishedResults, 'score'));
        $avg = empty($relatedData['finished']) ? 0 : $scores / $relatedData['finished'];
        $relatedData['avgScore'] = number_format($avg, 1);

        return $relatedData;
    }

    protected function findTestpapers($tasks, $type)
    {
        if (empty($tasks)) {
            return [$tasks, []];
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
            return [$activities, []];
        }

        return [$tasks, $testpapers];
    }

    protected function findTestpapersStatusNum($tasks)
    {
        $resultStatusNum = [];
        foreach ($tasks as $task) {
            if (empty($task['answerSceneId'])) {
                continue;
            }

            $answerRecords = $this->getAnswerRecordService()->search(
                ['answer_scene_id' => $task['answerSceneId']],
                [],
                0,
                $this->getAnswerRecordService()->count(['answer_scene_id' => $task['answerSceneId']])
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
        $routes = [
            'nextCheck' => [
                'course' => 'course_manage_exam_next_result_check',
                'classroom' => 'classroom_manage_exam_next_result_check',
            ],
        ];

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
