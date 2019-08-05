<?php

namespace AppBundle\Controller\Testpaper;

use ExamParser\Writer\WriteDocx;
use http\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use AppBundle\Common\Paginator;
use Biz\Task\Service\TaskService;
use Biz\Testpaper\TestpaperException;
use Biz\User\Service\UserService;
use AppBundle\Common\ArrayToolkit;
use Biz\Course\Service\CourseService;
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

        $conditions = array(
            'courseSetId' => $courseSet['id'],
            'type' => 'testpaper',
        );

        if ($courseSet['parentId'] > 0 && $courseSet['locked']) {
            $conditions['copyIdGT'] = 0;
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getTestpaperService()->searchTestpaperCount($conditions),
            10
        );

        $testpapers = $this->getTestpaperService()->searchTestpapers(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($testpapers, 'updatedUserId');
        $users = $this->getUserService()->findUsersByIds($userIds);

        $testpaperActivities = $this->getTestpaperActivityService()->findActivitiesByMediaIds(ArrayToolkit::column($testpapers, 'id'));

        return $this->render('testpaper/manage/index.html.twig', array(
            'courseSet' => $courseSet,
            'testpapers' => $testpapers,
            'users' => $users,
            'paginator' => $paginator,
            'testpaperActivities' => $testpaperActivities,
        ));
    }

    public function createAction(Request $request, $id)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($id);

        if ('POST' === $request->getMethod()) {
            $fields = $request->request->all();

            $fields['courseSetId'] = $courseSet['id'];
            $fields['courseId'] = 0;
            $fields['pattern'] = 'questionType';

            $testpaper = $this->getTestpaperService()->buildTestpaper($fields, 'testpaper');

            return $this->redirect(
                $this->generateUrl(
                    'course_set_manage_testpaper_questions',
                    array('courseSetId' => $courseSet['id'], 'testpaperId' => $testpaper['id'])
                )
            );
        }

        $types = $this->getQuestionTypes();

        $conditions = array(
            'types' => array_keys($types),
            'courseSetId' => $courseSet['id'],
            'parentId' => 0,
        );

        $questionNums = $this->getQuestionService()->getQuestionCountGroupByTypes($conditions);
        $questionNums = ArrayToolkit::index($questionNums, 'type');

        $user = $this->getUser();
        $ranges = $this->getTaskService()->findUserTeachCoursesTasksByCourseSetId($user['id'], $courseSet['id']);

        $manageCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSet['id']);

        return $this->render('testpaper/manage/create.html.twig', array(
            'courseSet' => $courseSet,
            'ranges' => $ranges,
            'types' => $types,
            'questionNums' => $questionNums,
            'courses' => $manageCourses,
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

    public function exportAction(Request $request, $courseSetId, $testpaperId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (empty($testpaper) || $testpaper['courseSetId'] != $courseSetId) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        $questions = $this->getTestpaperService()->buildExportTestpaperItems($testpaperId);

        $fileName = $testpaper['name'].'.docx';
        $baseDir = $this->get('kernel')->getContainer()->getParameter('topxia.disk.local_directory');
        $path = $baseDir.DIRECTORY_SEPARATOR.$fileName;

        $writer = new WriteDocx($path);
        $writer->write($questions);

        $headers = array(
            'Content-Type' => 'application/msword',
            'Content-Disposition' => 'attachment; filename='.$fileName,
        );

        return new BinaryFileResponse($path, 200, $headers);
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

        $testpaper = $this->getTestpaperService()->getTestpaper($result['testId'], $result['type']);
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
            if ($isContinue) {
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

    public function updateAction(Request $request, $courseSetId, $testpaperId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (empty($testpaper) || $testpaper['courseSetId'] != $courseSetId) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        if ('POST' === $request->getMethod()) {
            $data = $request->request->all();
            $this->getTestpaperService()->updateTestpaper($testpaper['id'], $data);

            $this->setFlashMessage('success', 'site.save.success');

            return $this->redirect($this->generateUrl('course_set_manage_testpaper', array('id' => $courseSet['id'])));
        }

        return $this->render('testpaper/manage/update.html.twig', array(
            'courseSet' => $courseSet,
            'testpaper' => $testpaper,
        ));
    }

    public function deleteAction(Request $request, $courseSetId, $testpaperId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (empty($testpaper) || $testpaper['courseSetId'] != $courseSetId) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        $this->getTestpaperService()->deleteTestpaper($testpaperId);

        return $this->createJsonResponse(true);
    }

    public function deletesAction(Request $request, $courseSetId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $ids = $request->request->get('ids');

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($ids);
        if (!empty($testpapers)) {
            foreach ($testpapers as $testpaper) {
                if ($testpaper['courseSetId'] != $courseSetId) {
                    return $this->createMessageResponse('error', 'testpaper not found');
                }
            }
            $this->getTestpaperService()->deleteTestpapers($ids);

            return $this->createJsonResponse(true);
        }

        return $this->createMessageResponse('error', 'testpaper not found');
    }

    public function publishAction(Request $request, $courseSetId, $testpaperId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (empty($testpaper) || $testpaper['courseSetId'] != $courseSetId) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $testpaper = $this->getTestpaperService()->publishTestpaper($testpaperId);

        $user = $this->getUserService()->getUser($testpaper['updatedUserId']);

        return $this->render('testpaper/manage/testpaper-list-tr.html.twig', array(
            'testpaper' => $testpaper,
            'user' => $user,
            'courseSet' => $courseSet,
        ));
    }

    public function closeAction(Request $request, $courseSetId, $testpaperId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (empty($testpaper) || $testpaper['courseSetId'] != $courseSetId) {
            $this->createNewException(TestpaperException::NOTFOUND_TESTPAPER());
        }

        $testpaper = $this->getTestpaperService()->closeTestpaper($testpaperId);

        $user = $this->getUserService()->getUser($testpaper['updatedUserId']);

        return $this->render('testpaper/manage/testpaper-list-tr.html.twig', array(
            'testpaper' => $testpaper,
            'user' => $user,
            'courseSet' => $courseSet,
        ));
    }

    public function questionsAction(Request $request, $courseSetId, $testpaperId)
    {
        $courseSet = $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (!$testpaper || $testpaper['courseSetId'] != $courseSetId) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        if ('draft' != $testpaper['status']) {
            return $this->createMessageResponse('error', '已发布或已关闭的试卷不能再修改题目');
        }

        if ('POST' === $request->getMethod()) {
            $fields = $request->request->all();

            if (empty($fields['questions'])) {
                return $this->createMessageResponse('error', '试卷题目不能为空！');
            }
            $fields['questions'] = json_decode($fields['questions'], true);

            if (empty($fields['questionTypeSeq'])) {
                return $this->createMessageResponse('error', '题型排序错误');
            }
            $fields['questionTypeSeq'] = json_decode($fields['questionTypeSeq'], true);

            if (count($fields['questions']) > 2000) {
                return $this->createMessageResponse('error', '试卷题目数量不能超过2000！');
            }

            if (!empty($fields['passedScore'])) {
                $fields['passedCondition'] = array($fields['passedScore']);
            }

            $this->getTestpaperService()->updateTestpaperItems($testpaper['id'], $fields);

            $this->setFlashMessage('success', 'site.save.success');

            return $this->createJsonResponse(array(
                'goto' => $this->generateUrl('course_set_manage_testpaper', array('id' => $courseSetId)),
            ));
        }

        $items = $this->getTestpaperService()->findItemsByTestId($testpaper['id']);
        $questions = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);

        $hasEssay = $this->getQuestionService()->hasEssay(ArrayToolkit::column($items, 'questionId'));

        if ($hasEssay) {
            $passedScoreDefault = empty($testpaper['passedCondition']) ? ceil($testpaper['score'] * 0.6) : $testpaper['passedCondition'][0];
        } else {
            $passedScoreDefault = ceil($testpaper['score'] * 0.6);
        }

        $user = $this->getUser();
        $manageCourses = $this->getCourseService()->findUserManageCoursesByCourseSetId($user['id'], $courseSet['id']);

        $courseTasks = $this->getQuestionRanges($testpaper['id']);

        return $this->render('testpaper/manage/question.html.twig', array(
            'courseSet' => $courseSet,
            'testpaper' => $testpaper,
            'questions' => $questions,
            'hasEssay' => $hasEssay,
            'passedScoreDefault' => $passedScoreDefault,
            'courseTasks' => $courseTasks,
            'courses' => $manageCourses,
            'subCounts' => empty($questions['material']) ? 0 : array_sum(array_column($questions['material'], 'subCount')),
        ));
    }

    public function infoAction(Request $request, $id)
    {
        $this->getCourseSetService()->tryManageCourseSet($id);

        $testpaperId = $request->request->get('testpaperId');

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);

        if (empty($testpaper) || $testpaper['courseSetId'] != $id) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        $items = $this->getTestpaperService()->getItemsCountByParams(
            array('testId' => $testpaperId, 'parentIdDefault' => 0),
            $gourpBy = 'questionType'
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

    public function previewAction(Request $request, $courseSetId, $testpaperId)
    {
        $this->getCourseSetService()->tryManageCourseSet($courseSetId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperId);
        if (!$testpaper || $testpaper['courseSetId'] != $courseSetId) {
            return $this->createMessageResponse('error', 'testpaper not found');
        }

        if ('closed' === $testpaper['status']) {
            return $this->createMessageResponse('warning', 'testpaper already closed');
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($testpaper['id']);

        $total = $this->getTestpaperService()->countQuestionTypes($testpaper, $questions);

        $attachments = $this->getTestpaperService()->findAttachments($testpaper['id']);

        return $this->render('testpaper/manage/preview.html.twig', array(
            'questions' => $questions,
            'limitedTime' => $testpaper['limitedTime'],
            'paper' => $testpaper,
            'paperResult' => array(),
            'total' => $total,
            'attachments' => $attachments,
            'questionTypes' => $this->getTestpaperService()->getCheckedQuestionTypeBySeq($testpaper),
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

        $question = ArrayToolkit::parts($question, array(
            'stem',
            'type',
            'options',
            'answer',
            'answers',
            'score',
            'missScore',
            'analysis',
            'attachment',
            'subQuestions',
            'difficulty',
            'errors',
        ));

        return $this->render("testpaper/subject/type/{$type}.html.twig", array(
            'question' => $question,
            'seq' => $seq,
            'token' => $token,
            'type' => $type,
            'isSub' => $isSub,
            'isTestpaper' => $isTestpaper,
            'method' => $method,
        ));
    }

    public function convertTemplateAction(Request $request)
    {
        $data = $request->request->all();
        $fromType = $data['fromType'];
        $toType = $data['toType'];
        $isSub = $data['isSub'];
        $isTestpaper = $data['isTestpaper'];
        $method = $request->request->get('method', 'edit');
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
            'isSub' => $isSub,
            'isTestpaper' => $isTestpaper,
            'method' => $method,
        ));
    }

    protected function convertEssay($toType, $question)
    {
        $question['type'] = $toType;
        if (in_array($toType, array('choice', 'single_choice', 'uncertain_choice'))) {
            $question['options'] = array();
            $question['answers'] = array();
        }
        $question = ArrayToolkit::parts($question, array(
            'stem',
            'type',
            'options',
            'answer',
            'answers',
            'score',
            'missScore',
            'analysis',
            'attachment',
            'subQuestions',
            'difficulty',
            'errors',
        ));

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
        $question = ArrayToolkit::parts($question, array(
            'stem',
            'type',
            'options',
            'answer',
            'answers',
            'score',
            'missScore',
            'analysis',
            'attachment',
            'subQuestions',
            'difficulty',
            'errors',
        ));

        return $question;
    }

    public function showTemplateAction(Request $request, $type)
    {
        $question = $request->request->get('question', array());
        $seq = $request->request->get('seq', 1);
        $token = $request->request->get('token', '');
        $isSub = $request->request->get('isSub', '0');

        $question = ArrayToolkit::parts($question, array(
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
            'subQuestions',
            'difficulty',
        ));

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
        $this->getCourseSetService()->tryManageCourseSet($data['courseSetId']);
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

    protected function getQuestionTypes()
    {
        $typesConfig = $this->get('extension.manager')->getQuestionTypes();

        $types = array();
        foreach ($typesConfig as $type => $typeConfig) {
            $types[$type] = array(
                'name' => $typeConfig['name'],
                'hasMissScore' => $typeConfig['hasMissScore'],
            );
        }

        return $types;
    }

    protected function getQuestionRanges($testpaperId)
    {
        $items = $this->getTestpaperService()->findItemsByTestId($testpaperId);
        $questionIds = ArrayToolkit::column($items, 'questionId');

        $questions = $this->getQuestionService()->findQuestionsByIds($questionIds);
        $taskIds = ArrayToolkit::column($questions, 'lessonId');

        $courseTasks = $this->getTaskService()->findTasksByIds($taskIds);
        $courseTasks = ArrayToolkit::index($courseTasks, 'id');

        return $courseTasks;
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
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
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

    protected function getFileService()
    {
        return $this->createService('Content:FileService');
    }

    protected function getTokenService()
    {
        return $this->createService('User:TokenService');
    }
}
