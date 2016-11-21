<?php
namespace WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Exception\AccessDeniedException;

class TestpaperController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getUser();

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->findTestpaperResultsCountByUserId($user['id']),
            10
        );

        $testpaperResults = $this->getTestpaperService()->findTestpaperResultsByUserId(
            $user['id'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        $testpapersIds     = ArrayToolkit::column($testpaperResults, 'testId');
        $testpapersTargets = ArrayToolkit::column($testpaperResults, 'target');
        $testpapers        = $this->getTestpaperService()->findTestpapersByIds($testpapersIds);
        $testpapers        = ArrayToolkit::index($testpapers, 'id');

        $targets   = ArrayToolkit::column($testpapers, 'target');
        $courseIds = array_map(function ($target) {
            $course = explode('/', $target);
            $course = explode('-', $course[0]);
            return $course[1];
        }, $targets);
        $lessonIds = array_map(function ($target) {
            $lesson = explode('/', $target);
            $lesson = explode('-', $lesson[1]);
            return $lesson[1];
        }, $testpapersTargets);

        foreach ($testpaperResults as $ke => &$value) {
            $value['lessonId'] = $lessonIds[$ke];
        }

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        return $this->render('TopxiaWebBundle:MyQuiz:my-quiz.html.twig', array(
            'myQuizActive'       => 'active',
            'user'               => $user,
            'myTestpaperResults' => $testpaperResults,
            'myTestpapers'       => $testpapers,
            'courses'            => $courses,
            'paginator'          => $paginator
        ));
    }

    public function doTestpaperAction(Request $request, $testId, $lessonId)
    {
        $user = $this->getUser();

        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        if (empty($testpaper)) {
            throw $this->createResourceNotFoundException('testpaper', $testId);
        }

        if ($testpaper['status'] == 'draft') {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('该试卷未发布，如有疑问请联系老师！'));
        }

        if ($testpaper['status'] == 'closed') {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('该试卷已关闭，如有疑问请联系老师！'));
        }

        $testpaperResult = $this->getTestpaperService()->startTestpaper($testpaper['id'], $lessonId);

        if (in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return $this->redirect($this->generateUrl('testpaper_show', array('resultId' => $testpaperResult['id'])));
        } else {
            return $this->redirect($this->generateUrl('testpaper_result_show', array('resultId' => $testpaperResult['id'])));
        }
    }

    public function reDoTestpaperAction(Request $request, $targetType, $targetId, $testId)
    {
        $userId = $this->getUser()->id;

        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        if (empty($testpaper)) {
            throw $this->createResourceNotFoundException('testpaper', $testId);
        }

        $testResult = $this->getTestpaperService()->findTestpaperResultsByTestIdAndStatusAndUserId($testId, $userId, array('doing', 'paused'));

        if ($testResult) {
            return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testResult['id'])));
        }

        if ($testpaper['status'] == 'draft') {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('该试卷未发布，如有疑问请联系老师！'));
        }

        if ($testpaper['status'] == 'closed') {
            return $this->createMessageResponse('info', $this->getServiceKernel()->trans('该试卷已关闭，如有疑问请联系老师！'));
        }

        $testResult = $this->getTestpaperService()->findTestpaperResultsByTestIdAndStatusAndUserId($testId, $userId, array('reviewing'));

        if (!empty($testResult)) {
            throw new AccessDeniedException($this->getServiceKernel()->trans('试卷还在批阅中'));
        }

        $testResult = $this->getTestpaperService()->startTestpaper($testId, array('type' => $targetType, 'id' => $targetId));

        return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testResult['id'])));
    }

    public function realTimeCheckAction(Request $request)
    {
        $testId = $request->query->get('value');

        $testPaper = $this->getTestpaperService()->getTestpaper($testId);

        if (empty($testPaper)) {
            $response = array('success' => false, 'message' => $this->getServiceKernel()->trans('试卷不存在'));
            return $this->createJsonResponse($response);
        }

        if ($testPaper['limitedTime'] == 0) {
            $response = array('success' => false, 'message' => $this->getServiceKernel()->trans('该试卷考试时间未限制,请选择其他限制时长的试卷'));
        } else {
            $response = array('success' => true, 'message' => '');
        }

        return $this->createJsonResponse($response);
    }

    public function previewTestAction(Request $request, $testId)
    {
        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        if (!$teacherId = $this->getTestpaperService()->canTeacherCheck($testpaper['id'])) {
            throw new AccessDeniedException($this->getServiceKernel()->trans('无权预览试卷！'));
        }

        $items = $this->getTestpaperService()->previewTestpaper($testId);

        $total       = $this->makeTestpaperTotal($testpaper, $items);
        $attachments = $this->findAttachments($testpaper['id']);

        return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-show.html.twig', array(
            'items'       => $items,
            'limitTime'   => $testpaper['limitedTime'] * 60,
            'paper'       => $testpaper,
            'id'          => 0,
            'isPreview'   => 'preview',
            'total'       => $total,
            'attachments' => $attachments
        ));
    }

    public function doTestAction(Request $request, $resultId)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($resultId);

        if (in_array($testpaperResult['status'], array('reviewing', 'finished'))) {
            return $this->redirect($this->generateUrl('testpaper_result_show', array('resultId' => $testpaperResult['id'])));
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        $canLookTestpaper = $this->getTestpaperService()->canLookTestpaper($testpaperResult['id']);
        $questions        = $this->getTestpaperService()->showTestpaperItems($testpaperResult['id']);

        $total = $this->makeTestpaperTotal($testpaper, $questions);

        $favorites = $this->getQuestionService()->findAllFavoriteQuestionsByUserId($testpaperResult['userId']);

        //限时考试
        $target = array();
        $lesson = $this->getCourseService()->getLesson($testpaperResult['lessonId']);
        if ($lesson['testMode'] == 'realTime') {
            $testpaperResult['usedTime'] = time() - $lesson['testStartTime'];
        }

        $attachments = $this->findAttachments($testpaper['id']);

        return $this->render('WebBundle:Testpaper:start-do-show.html.twig', array(
            'questions'     => $questions,
            'limitTime'     => $testpaperResult['limitedTime'] * 60,
            'paper'         => $testpaper,
            'paperResult'   => $testpaperResult,
            'favorites'     => ArrayToolkit::column($favorites, 'questionId'),
            'total'         => $total,
            'attachments'   => $attachments,
            'questionTypes' => $this->getCheckedQuestionType($testpaper),
            'showTypeBar'   => 1,
            'showHeader'    => 0
        ));
    }

    protected function getCheckedQuestionType($testpaper)
    {
        $questionTypes = array();
        foreach ($testpaper['metas']['counts'] as $type => $count) {
            if ($count > 0) {
                $questionTypes[] = $type;
            }
        }

        return $questionTypes;
    }

    protected function findAttachments($testId)
    {
        $items       = $this->getTestpaperService()->findItemsByTestId($testId);
        $questionIds = ArrayToolkit::column($items, 'questionId');
        $conditions  = array(
            'type'        => 'attachment',
            'targetTypes' => array('question.stem', 'question.analysis'),
            'targetIds'   => $questionIds
        );
        $attachments = $this->getUploadFileService()->searchUseFiles($conditions);
        array_walk($attachments, function (&$attachment) {
            $attachment['dkey'] = $attachment['targetType'].$attachment['targetId'];
        });

        return ArrayToolkit::group($attachments, 'dkey');
    }

    public function showResultAction(Request $request, $resultId)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($resultId);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        if (!$testpaper) {
            throw $this->createResourceNotFoundException('testpaper', $testpaperResult['testId']);
        }

        if (in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testpaperResult['id'])));
        }

        $canLookTestpaper = $this->getTestpaperService()->canLookTestpaper($testpaperResult['id']);

        if (!$canLookTestpaper) {
            throw new AccessDeniedException($this->getServiceKernel()->trans('无权查看试卷！'));
        }

        $builder   = $this->getTestpaperService()->getTestpaperBuilder($testpaper['type']);
        $questions = $builder->showTestItems($testpaperResult['id']);

        $accuracy = $this->getTestpaperService()->makeAccuracy($testpaperResult['id']);

        $total = $this->makeTestpaperTotal($testpaper, $questions);

        //$favorites = $this->getQuestionService()->findAllFavoriteQuestionsByUserId($testpaperResult['userId']);

        $student = $this->getUserService()->getUser($testpaperResult['userId']);

        $attachments = $this->findAttachments($testpaper['id']);
        return $this->render('WebBundle:Testpaper:result.html.twig', array(
            'questions'     => $questions,
            'accuracy'      => $accuracy,
            'paper'         => $testpaper,
            'paperResult'   => $testpaperResult,
            //'favorites'   => ArrayToolkit::column($favorites, 'questionId'),
            'total'         => $total,
            'student'       => $student,
            'source'        => $request->query->get('source', 'course'),
            'attachments'   => $attachments,
            'questionTypes' => $this->getCheckedQuestionType($testpaper)
        ));
    }

    public function testSuspendAction(Request $request, $id)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);

        if (!$testpaperResult) {
            throw $this->createResourceNotFoundException('testpaper', $testpaperResult['testId']);
        }

//权限！

        if ($testpaperResult['userId'] != $this->getUser()->id) {
            throw new AccessDeniedException($this->getServiceKernel()->trans('不可以访问其他学生的试卷哦~'));
        }

        if ($request->getMethod() == 'POST') {
            $data     = $request->request->all();
            $answers  = array_key_exists('data', $data) ? $data['data'] : array();
            $usedTime = $data['usedTime'];

            $results = $this->getTestpaperService()->submitTestpaperAnswer($id, $answers);

            $this->getTestpaperService()->updateTestpaperResult($id, $usedTime);

            return $this->createJsonResponse(true);
        }
    }

    public function submitTestAction(Request $request, $resultId)
    {
        if ($request->getMethod() == 'POST') {
            $data     = $request->request->all();
            $answers  = array_key_exists('data', $data) ? $data['data'] : array();
            $usedTime = $data['usedTime'];

            $results = $this->getTestpaperService()->submitTestpaperAnswer($id, $answers);

            $this->getTestpaperService()->updateTestpaperResult($resultId, $usedTime);

            return $this->createJsonResponse(true);
        }
    }

    public function finishTestAction(Request $request, $resultId)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($resultId);

        if (!empty($testpaperResult) && !in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return $this->createJsonResponse(true);
        }

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();

            $this->getTestpaperService()->finishTest($testpaperResult['id'], $formData);

            return $this->createJsonResponse(true);
        }
    }

    public function teacherCheckAction(Request $request, $id)
    {
        //身份校验?

        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        if (!$testpaper) {
            throw $this->createResourceNotFoundException('testpaper', $testpaperResult['testId']);
        }

        if (!$teacherId = $this->getTestpaperService()->canTeacherCheck($testpaper['id'])) {
            throw new AccessDeniedException($this->getServiceKernel()->trans('无权批阅试卷！'));
        }

        if ($testpaperResult['status'] != 'reviewing') {
            return $this->redirect($this->generateUrl('course_manage_test_results', array('id' => $testpaperResult['id'])));
        }

        if ($request->getMethod() == 'POST') {
            $form = $request->request->all();

            $testpaperResult = $this->getTestpaperService()->makeTeacherFinishTest($id, $testpaper['id'], $teacherId, $form);

            $user = $this->getUser();

            $message = array(
                'id'       => $testpaperResult['id'],
                'name'     => $testpaperResult['paperName'],
                'userId'   => $user['id'],
                'userName' => $user['nickname'],
                'type'     => 'read'
            );

            $result = $this->getNotificationService()->notify($testpaperResult['userId'], 'test-paper', $message);

            return $this->createJsonResponse(true);
        }

        //$result   = $this->getTestpaperService()->showTestpaper($id, true);
        $items    = $result['formatItems'];
        $accuracy = $result['accuracy'];

        $total = $this->makeTestpaperTotal($testpaper, $items);

        $types = array();

        if (in_array('essay', $testpaper['metas']['question_type_seq'])) {
            array_push($types, 'essay');
        }

        if (in_array('material', $testpaper['metas']['question_type_seq'])) {
            foreach ($items['material'] as $key => $item) {
                $questionTypes = ArrayToolkit::index(empty($item['items']) ? array() : $item['items'], 'questionType');

                if (array_key_exists('essay', $questionTypes)) {
                    if (!in_array('material', $types)) {
                        array_push($types, 'material');
                    }
                }
            }
        }

        $student = $this->getUserService()->getUser($testpaperResult['userId']);

        $questionsSetting = $this->getSettingService()->get('questions', array());

        return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-review.html.twig', array(
            'items'            => $items,
            'accuracy'         => $accuracy,
            'paper'            => $testpaper,
            'paperResult'      => $testpaperResult,
            'id'               => $id,
            'total'            => $total,
            'types'            => $types,
            'student'          => $student,
            'questionsSetting' => $questionsSetting,
            'source'           => $request->query->get('source', 'course'),
            'targetId'         => $request->query->get('targetId', 0)
        ));
    }

    public function pauseTestAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:QuizQuestionTest:do-test-pause-modal.html.twig');
    }

    protected function makeTestpaperTotal($testpaper, $items)
    {
        $total = array();

        foreach ($testpaper['metas']['counts'] as $type => $count) {
            if (empty($items[$type])) {
                $total[$type]['score']     = 0;
                $total[$type]['number']    = 0;
                $total[$type]['missScore'] = 0;
            } else {
                $total[$type]['score']  = array_sum(ArrayToolkit::column($items[$type], 'score'));
                $total[$type]['number'] = count($items[$type]);

                if (array_key_exists('missScore', $testpaper['metas']) && array_key_exists($type, $testpaper["metas"]["missScore"])) {
                    $total[$type]['missScore'] = $testpaper["metas"]["missScore"][$type];
                } else {
                    $total[$type]['missScore'] = 0;
                }
            }
        }

        return $total;
    }

    public function listReviewingTestAction(Request $request)
    {
        $user = $this->getUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', $this->getServiceKernel()->trans('您不是老师，不能查看此页面！'));
        }

        $courses      = $this->getCourseService()->findUserTeachCourses(array('userId' => $user['id']), 0, PHP_INT_MAX, false);
        $courseIds    = ArrayToolkit::column($courses, 'id');
        $testpapers   = $this->getTestpaperService()->findAllTestpapersByTargets($courseIds);
        $testpaperIds = ArrayToolkit::column($testpapers, 'id');

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->findTestpaperResultCountByStatusAndTestIds($testpaperIds, 'reviewing'),
            10
        );

        $paperResults = $this->getTestpaperService()->searchTestpaperResults(
            array(
                'testIds' => $testpaperIds,
                'status'  => 'reviewing'
            ),
            array(
                'checkedTime',
                'DESC'
            ),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $testpaperIds = ArrayToolkit::column($paperResults, 'testId');

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpaperIds);

        $userIds = ArrayToolkit::column($paperResults, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $targets   = ArrayToolkit::column($testpapers, 'target');
        $courseIds = array_map(function ($target) {
            $course = explode('/', $target);
            $course = explode('-', $course[0]);
            return $course[1];
        }, $targets);

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        return $this->render('TopxiaWebBundle:MyQuiz:teacher-test-layout.html.twig', array(
            'status'       => 'reviewing',
            'users'        => ArrayToolkit::index($users, 'id'),
            'paperResults' => $paperResults,
            'courses'      => ArrayToolkit::index($courses, 'id'),
            'testpapers'   => ArrayToolkit::index($testpapers, 'id'),
            'teacher'      => $user,
            'paginator'    => $paginator
        ));
    }

    public function listFinishedTestAction(Request $request)
    {
        $user = $this->getUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', $this->getServiceKernel()->trans('您不是老师，不能查看此页面！'));
        }

        $courses      = $this->getCourseService()->findUserTeachCourses(array('userId' => $user['id']), 0, PHP_INT_MAX, false);
        $courseIds    = ArrayToolkit::column($courses, 'id');
        $testpapers   = $this->getTestpaperService()->findAllTestpapersByTargets($courseIds);
        $testpaperIds = ArrayToolkit::column($testpapers, 'id');

        $conditions = array(
            'testIds'        => $testpaperIds,
            'status'         => 'finished',
            'checkTeacherId' => $user['id']
        );

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->searchTestpaperResultsCount($conditions),
            10
        );

        $paperResults = $this->getTestpaperService()->searchTestpaperResults(
            $conditions,
            array(
                'checkedTime',
                'DESC'
            ),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $testpaperIds = ArrayToolkit::column($paperResults, 'testId');

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpaperIds);

        $userIds = ArrayToolkit::column($paperResults, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $targets   = ArrayToolkit::column($testpapers, 'target');
        $courseIds = array_map(function ($target) {
            $course = explode('/', $target);
            $course = explode('-', $course[0]);
            return $course[1];
        }, $targets);

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        return $this->render('TopxiaWebBundle:MyQuiz:teacher-test-layout.html.twig', array(
            'status'       => 'finished',
            'users'        => ArrayToolkit::index($users, 'id'),
            'paperResults' => $paperResults,
            'courses'      => ArrayToolkit::index($courses, 'id'),
            'testpapers'   => ArrayToolkit::index($testpapers, 'id'),
            'teacher'      => $user,
            'paginator'    => $paginator
        ));
    }

    public function teacherCheckInCourseAction(Request $request, $id, $status)
    {
        $user = $this->getUser();

        $course = $this->getCourseService()->tryManageCourse($id);

        $testpapers = $this->getTestpaperService()->findAllTestpapersByTarget($id);

        $testpaperIds = ArrayToolkit::column($testpapers, 'id');

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->findTestpaperResultCountByStatusAndTestIds($testpaperIds, $status),
            10
        );

        $testpaperResults = $this->getTestpaperService()->searchTestpaperResults(
            array(
                'testIds' => $testpaperIds,
                'status'  => $status
            ),
            array(
                'checkedTime',
                'DESC'
            ),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($testpaperResults, 'userId'));

        $teacherIds = ArrayToolkit::column($testpaperResults, 'checkTeacherId');

        $teachers = $this->getUserService()->findUsersByIds($teacherIds);

        return $this->render('TopxiaWebBundle:MyQuiz:list-course-test-paper.html.twig', array(
            'status'       => $status,
            'testpapers'   => ArrayToolkit::index($testpapers, 'id'),
            'paperResults' => ArrayToolkit::index($testpaperResults, 'id'),
            'course'       => $course,
            'users'        => $users,
            'teachers'     => ArrayToolkit::index($teachers, 'id'),
            'paginator'    => $paginator,
            'isTeacher'    => $this->getCourseService()->hasTeacherRole($id, $user['id']) || $user->isSuperAdmin()
        ));
    }

    public function userResultJsonAction(Request $request, $id)
    {
        $user = $this->getUser()->id;

        if (empty($user)) {
            return $this->createJsonResponse(array('error' => $this->getServiceKernel()->trans('您尚未登录系统或登录已超时，请先登录。')));
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($id);

        if (empty($testpaper)) {
            return $this->createJsonResponse(array('error' => $this->getServiceKernel()->trans('试卷已删除，请联系管理员。')));
        }

        $testResult = $this->getTestpaperService()->findTestpaperResultByTestpaperIdAndUserIdAndActive($id, $user);

        if (empty($testResult)) {
            return $this->createJsonResponse(array('status' => 'nodo'));
        }

        $testResult['totalScore'] = $testpaper['score'];

        return $this->createJsonResponse($testResult);
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
