<?php

namespace AppBundle\Controller\Testpaper;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\ExportHelp;
use AppBundle\Common\Paginator;
use AppBundle\Controller\Testpaper\BaseTestpaperController as BaseController;
use Biz\Activity\ActivityException;
use Biz\Activity\Service\ActivityService;
use Biz\Activity\Service\HomeworkActivityService;
use Biz\Activity\Service\TestpaperActivityService;
use Biz\Classroom\Service\ClassroomService;
use Biz\Common\CommonException;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\File\UploadFileException;
use Biz\QuestionBank\QuestionBankException;
use Biz\QuestionBank\Service\QuestionBankService;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\TestpaperException;
use Biz\User\Service\TokenService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerRecordService;
use Codeages\Biz\ItemBank\Answer\Service\AnswerReportService;
use Codeages\Biz\ItemBank\Assessment\Service\AssessmentService;
use http\Exception\InvalidArgumentException;
use PhpOffice\PhpWord\Exception\Exception;
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
            ['seq' => 'ASC', 'id' => 'ASC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        list($tasks, $testpapers) = $this->getTaskService()->findTestpapers($tasks, $type);

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
            'type' => $type,
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

        $orderBy = in_array($status, ['reviewing', 'finished']) ? ['end_time' => 'ASC'] : ['updated_time' => 'DESC'];
        $answerRecords = $this->getAnswerRecordService()->search(
            $conditions,
            $orderBy,
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
            'answerRecords' => $this->filterAnswerRecordsSubmitNum($answerRecords, $answerScene['id']),
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

    protected function filterAnswerRecordsSubmitNum($answerRecords, $answerSceneId)
    {
        $orderedSubmitRecords = $this->getActivityService()->orderAssessmentSubmitNumber(ArrayToolkit::column($answerRecords, 'user_id'), $answerSceneId);
        foreach ($answerRecords as &$answerRecord) {
            $answerRecord['submit_num'] = $orderedSubmitRecords[$answerRecord['id']]['submit_num'];
        }

        return $answerRecords;
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

    public function transcriptDataAction(Request $request, $courseId, $testpaperId, $activityId)
    {
        $this->getCourseService()->tryManageCourse($courseId);

        $testpaper = $this->getAssessmentService()->getAssessment($testpaperId);
        if (!$testpaper) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $activity = $this->getActivityService()->getActivity($activityId);
        if (!$activity) {
            $this->createNewException(ActivityException::NOTFOUND_ACTIVITY());
        }

        list($start, $limit, $exportAllowCount) = ExportHelp::getMagicExportSetting($request);

        list($title, $records, $memberCount) = $this->getExportData(
            $activity,
            $start,
            $limit,
            $exportAllowCount
        );

        $file = '';
        if (0 == $start) {
            $file = ExportHelp::addFileTitle($request, $activity['mediaType'].'_students_transcript', $title);
        }

        $content = implode("\r\n", $records);
        $file = ExportHelp::saveToTempFile($request, $content, $file);
        $status = ExportHelp::getNextMethod($start + $limit, $memberCount);

        return $this->createJsonResponse(
            [
                'status' => $status,
                'fileName' => $file,
                'start' => $start + $limit,
            ]
        );
    }

    protected function getExportData($activity, $start, $limit, $exportAllowCount)
    {
        $answerScene = $this->getAnswerSceneByActivityId($activity['id']);
        $conditions = ['answer_scene_id' => $answerScene['id']];
        $recordCount = $this->getAnswerRecordService()->count($conditions);

        $recordCount = ($recordCount > $exportAllowCount) ? $exportAllowCount : $recordCount;
        if ($recordCount < ($start + $limit + 1)) {
            $limit = $recordCount - $start;
        }
        $answerRecords = $this->getAnswerRecordService()->search($conditions, ['end_time' => 'ASC'], $start, $limit);

        $answerReports = ArrayToolkit::index($this->getAnswerReportService()->findByIds(ArrayToolkit::column($answerRecords, 'answer_report_id')), 'id');
        $studentIds = ArrayToolkit::column($answerRecords, 'user_id');
        $teacherIds = ArrayToolkit::column($answerReports, 'review_user_id');
        $userIds = array_values(array_unique(array_merge($studentIds, $teacherIds)));
        $users = $this->getUserService()->findUsersByIds($userIds);
        $profiles = $this->getUserService()->findUserProfilesByIds($userIds);
        $profiles = ArrayToolkit::index($profiles, 'id');

        $str = $this->getExportFieldTitle($activity['mediaType']);

        $records = [];
        $answerRecords = ArrayToolkit::group($answerRecords, 'user_id');
        foreach ($answerRecords as $userAnswerRecord) {
            foreach ($userAnswerRecord as $index => $answerRecord) {
                $answerReport = $answerReports[$answerRecord['answer_report_id']];
                $member = '';
                $member .= is_numeric($users[$answerRecord['user_id']]['nickname']) ? $users[$answerRecord['user_id']]['nickname']."\t".',' : $users[$answerRecord['user_id']]['nickname'].',';
                $member .= $profiles[$answerRecord['user_id']]['truename'] ? $profiles[$answerRecord['user_id']]['truename'].',' : '-'.',';
                $member .= $users[$answerRecord['user_id']]['verifiedMobile'] ? $users[$answerRecord['user_id']]['verifiedMobile'].',' : '-'.',';
                $member .= $users[$answerRecord['user_id']]['emailVerified'] ? $users[$answerRecord['user_id']]['email'].',' : '-'.',';
                $member .= date('Y-m-d H:i:s', $answerRecord['begin_time'])."\t".',';
                $member .= $this->timeFormatterFilter($answerRecord['used_time']).',';
                $member .= $this->trans('course.homework_check.review.submit_num_detail', ['%num%' => $index + 1]).',';
                $member .= $this->getReviewStatus($answerRecord['status']).',';
                $member .= $answerReport['score'].',';
                $member .= $answerScene['pass_score'].',';
                $member .= $this->getPassStatus($answerReport['grade']).',';
                $reviewer = $this->getReviewer($users[$answerReport['review_user_id']], $answerRecord);
                $member .= is_numeric($reviewer) ? $reviewer."\t".',' : $reviewer.','; //批阅人
                $member .= $answerReport['comment'] ? $answerReport['comment'].',' : '-'.','; //教师评语

                $records[] = $member;
            }
        }

        return [$str, $records, $recordCount];
    }

    protected function getExportFieldTitle($type)
    {
        $str = [
            'homework' => '用户名,姓名,手机号,邮箱,作业时间（年-月-日-时-分-秒）,作业用时（时-分-秒）,作业次数,状态,作业成绩,合格成绩,通过状态,批阅人,教师评语',
            'testpaper' => '用户名,姓名,手机号,邮箱,开考时间（年-月-日-时-分-秒）,考试用时（时-分-秒）,考试次数,状态,本次成绩,通过成绩,通过状态,批阅人,教师评语',
        ];

        return $str[$type];
    }

    public function timeFormatterFilter($time)
    {
        if ($time <= 60) {
            return $this->trans('site.twig.extension.time_interval.second', ['%diff%' => $time]);
        }

        if ($time <= 3600) {
            return $this->trans('site.twig.extension.time_interval.minute', ['%diff%' => round($time / 60)]);
        }

        return $this->trans('site.twig.extension.time_interval.hour_minute', ['%diff_hour%' => floor($time / 3600), '%diff_minute%' => round($time % 3600 / 60)]);
    }

    protected function getReviewStatus($status)
    {
        if ('doing' == $status) {
            return $this->trans('site.default.doing');
        } elseif ('reviewing' == $status) {
            return $this->trans('site.default.unreviewing');
        } else {
            return $this->trans('site.default.reviewing');
        }
    }

    protected function getPassStatus($status)
    {
        switch ($status) {
            case 'excellent':
                $passStatus = $this->trans('优秀');
                break;
            case 'good':
                $passStatus = $this->trans('良好');
                break;
            case 'passed':
                $passStatus = $this->trans('通过');
                break;
            case 'unpassed':
                $passStatus = $this->trans('不通过');
                break;
            default:
                $passStatus = '-';
                break;
        }

        return $passStatus;
    }

    protected function getReviewer($user, $answerRecord)
    {
        $reviewer = '-';
        if ('finished' == $answerRecord['status']) {
            $reviewer = $user ? $user['nickname'] : $this->trans('course.homework_check.review.system_review');
        }

        return $reviewer;
    }

    public function transcriptExportAction(Request $request, $courseId, $testpaperId, $activityId)
    {
        $activity = $this->getActivityService()->getActivity($activityId);
        if (!$activity) {
            $this->createNewException(ActivityException::NOTFOUND_ACTIVITY());
        }
        $fileName = sprintf('%s_%s结果_%s.csv', $activity['title'], 'testpaper' == $activity['mediaType'] ? $this->trans('testpaper.check.homework') : $this->trans('testpaper.check.testpaper'), date('Y-n-d'));

        return ExportHelp::exportCsv($request, $fileName);
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

        $answerScene = $this->getAnswerSceneService()->get($activity['ext']['answerScene']['id']);
        $needJob = 0; //是否要通过Job更新默认不需要
        //判断如果存在新提交的内容
        if (empty($answerScene['question_report_update_time']) || $answerScene['question_report_update_time'] < $answerScene['last_review_time']) {
            $answerCount = $this->getAnswerRecordService()->count(['answer_scene_id' => $activity['ext']['answerScene']['id'], 'status' => 'finished']);
            $needJob = $this->needSyncJob($answerCount, $activity['ext']['testpaper']['question_count']);
            if (!$needJob) {
                //判断当前阈值不需要定时任务来异步处理
                !empty($answerCount) ? $this->getAnswerSceneService()->buildAnswerSceneReport($activity['ext']['answerScene']['id']) : null;
            } else {
                $jobSync = $this->handleJob($answerScene); //是否在次请求加载过程中存在同步执行中的Job
            }
        }

        $answerSceneReport = $this->getAnswerSceneService()->getAnswerSceneReport($activity['ext']['answerScene']['id']);

        return $this->render('testpaper/manage/result-analysis.html.twig', [
            'activity' => $activity,
            'studentNum' => $studentNum,
            'answerSceneReport' => $answerSceneReport,
            'assessment' => $activity['ext']['testpaper'],
            'targetType' => $targetType,
            'answerScene' => $answerScene,
            'jobSync' => !empty($jobSync) ? 1 : 0,
            'needJob' => !empty($needJob) ? 1 : 0,
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

        if (preg_match('/\/|\\\\/i', $toType, $matches)) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

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
        $assessment = $this->getAssessmentService()->importAssessment($assessment);

        return $this->createJsonResponse(['goto' => $this->generateUrl('question_bank_manage_testpaper_edit', ['id' => $questionBank['id'], 'assessmentId' => $assessment['id'], 'isImport' => 1])]);
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

    /**
     * @return TokenService
     */
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

    /**
     * @return AnswerRecordService
     */
    protected function getAnswerRecordService()
    {
        return $this->createService('ItemBank:Answer:AnswerRecordService');
    }

    /**
     * @return AnswerReportService
     */
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
