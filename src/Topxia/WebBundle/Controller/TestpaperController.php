<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Service\Task\TaskProcessor\TaskProcessorFactory;

class TestpaperController extends BaseController
{
    public function indexAction(Request $request)
    {
        $user = $this->getCurrentUser();

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

    public function doTestpaperAction(Request $request, $targetType, $targetId, $testId)
    {
        $userId = $this->getCurrentUser()->id;

        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        if (empty($testpaper)) {
            throw $this->createNotFoundException();
        }

//学习计划任务

        if ($this->isPluginInstalled('ClassroomPlan')) {
            $taskProcessor = $this->getTaskProcessor('studyPlan');
            $canFinish     = $taskProcessor->canFinish($targetId, 'testpaper', $userId);

            if (!$canFinish) {
                return $this->createMessageResponse('info', '在该任务之前，还有学习任务没有完成哦！');
            }
        }

        $testpaperResult = $this->getTestpaperService()->findTestpaperResultByTestpaperIdAndUserIdAndActive($testId, $userId);

        if (empty($testpaperResult)) {
            if ($testpaper['status'] == 'draft') {
                return $this->createMessageResponse('info', '该试卷未发布，如有疑问请联系老师！');
            }

            if ($testpaper['status'] == 'closed') {
                return $this->createMessageResponse('info', '该试卷已关闭，如有疑问请联系老师！');
            }

            $testpaperResult = $this->getTestpaperService()->startTestpaper($testId, array('type' => $targetType, 'id' => $targetId));

            return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testpaperResult['id'])));
        }

        if (in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testpaperResult['id'])));
        } else {
            return $this->redirect($this->generateUrl('course_manage_test_results', array('id' => $testpaperResult['id'])));
        }
    }

    public function reDoTestpaperAction(Request $request, $targetType, $targetId, $testId)
    {
        $userId = $this->getCurrentUser()->id;

        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        if (empty($testpaper)) {
            throw $this->createNotFoundException();
        }

        $testResult = $this->getTestpaperService()->findTestpaperResultsByTestIdAndStatusAndUserId($testId, $userId, array('doing', 'paused'));

        if ($testResult) {
            return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testResult['id'])));
        }

        if ($testpaper['status'] == 'draft') {
            return $this->createMessageResponse('info', '该试卷未发布，如有疑问请联系老师！');
        }

        if ($testpaper['status'] == 'closed') {
            return $this->createMessageResponse('info', '该试卷已关闭，如有疑问请联系老师！');
        }

        $testResult = $this->getTestpaperService()->findTestpaperResultsByTestIdAndStatusAndUserId($testId, $userId, array('reviewing'));

        if (!empty($testResult)) {
            throw $this->createAccessDeniedException("试卷还在批阅中");
        }

        $testResult = $this->getTestpaperService()->startTestpaper($testId, array('type' => $targetType, 'id' => $targetId));

        return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testResult['id'])));
    }

    public function realTimeCheckAction(Request $request)
    {
        $testId = $request->query->get('value');

        $testPaper = $this->getTestpaperService()->getTestpaper($testId);

        if (empty($testPaper)) {
            $response = array('success' => false, 'message' => '试卷不存在');
            return $this->createJsonResponse($response);
        }

        if ($testPaper['limitedTime'] == 0) {
            $response = array('success' => false, 'message' => '该试卷考试时间未限制,请选择其他限制时长的试卷');
        } else {
            $response = array('success' => true, 'message' => '');
        }

        return $this->createJsonResponse($response);
    }

    public function previewTestAction(Request $request, $testId)
    {
        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        if (!$teacherId = $this->getTestpaperService()->canTeacherCheck($testpaper['id'])) {
            throw $this->createAccessDeniedException('无权预览试卷！');
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

    public function showTestAction(Request $request, $id)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);

        if (in_array($testpaperResult['status'], array('reviewing', 'finished'))) {
            return $this->redirect($this->generateUrl('course_manage_test_results', array('id' => $testpaperResult['id'])));
        }
        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        $canLookTestpaper = $this->getTestpaperService()->canLookTestpaper($id);
        $result           = $this->getTestpaperService()->showTestpaper($id);
        $items            = $result['formatItems'];
        $total            = $this->makeTestpaperTotal($testpaper, $items);

        $favorites = $this->getQuestionService()->findAllFavoriteQuestionsByUserId($testpaperResult['userId']);
        $targets   = $this->get('topxia.target_helper')->getTargets(array($testpaperResult['target']));

        //试卷对应的考试（课时）
        $target = array();

        if ($targets[$testpaperResult['target']]['type'] == 'lesson') {
            $target = $this->getCourseService()->getLesson($targets[$testpaperResult['target']]['id']);

            if ($target['testMode'] == 'realTime') {
                $testpaperResult['usedTime'] = time() - $target['testStartTime'];
            }
        }
        $attachments = $this->findAttachments($testpaper['id']);

        return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-show.html.twig', array(
            'items'       => $items,
            'limitTime'   => $testpaperResult['limitedTime'] * 60,
            'paper'       => $testpaper,
            'paperResult' => $testpaperResult,
            'favorites'   => ArrayToolkit::column($favorites, 'questionId'),
            'id'          => $id,
            'total'       => $total,
            'target'      => $target,
            'attachments' => $attachments
        ));
    }

    private function findAttachments($testId)
    {
        $items       = $this->getTestpaperService()->getTestpaperItems($testId);
        $questionIds = ArrayToolkit::column($items, 'questionId');
        $conditions  = array(
            'type'        => 'attachment',
            'targetTypes' => array('question.stem', 'question.analysis'),
            'targetIds'   => $questionIds
        );
        $attachments = $this->geUploadFileService()->searchUseFiles($conditions);
        array_walk($attachments, function (&$attachment) {
            $attachment['dkey'] = $attachment['targetType'].$attachment['targetId'];
        });

        return ArrayToolkit::group($attachments, 'dkey');
    }

    public function testResultAction(Request $request, $id)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        if (!$testpaper) {
            throw $this->createNotFoundException("试卷不存在");
        }

        if (in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testpaperResult['id'])));
        }

        $testpaper        = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);
        $canLookTestpaper = $this->getTestpaperService()->canLookTestpaper($id);

        if (!$canLookTestpaper) {
            throw $this->createAccessDeniedException('无权查看试卷！');
        }

        $result   = $this->getTestpaperService()->showTestpaper($id, true);
        $items    = $result['formatItems'];
        $accuracy = $result['accuracy'];

        $total = $this->makeTestpaperTotal($testpaper, $items);

        $favorites = $this->getQuestionService()->findAllFavoriteQuestionsByUserId($testpaperResult['userId']);

        $student = $this->getUserService()->getUser($testpaperResult['userId']);

        $targets = $this->get('topxia.target_helper')->getTargets(array($testpaperResult['target']));

        //获取试卷载体
        $target = array();

        if ($targets[$testpaperResult['target']]['type'] == 'lesson') {
            $target = $this->getCourseService()->getLesson($targets[$testpaperResult['target']]['id']);
        }
        $attachments = $this->findAttachments($testpaper['id']);
        return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-result.html.twig', array(
            'items'       => $items,
            'accuracy'    => $accuracy,
            'paper'       => $testpaper,
            'paperResult' => $testpaperResult,
            'favorites'   => ArrayToolkit::column($favorites, 'questionId'),
            'id'          => $id,
            'total'       => $total,
            'student'     => $student,
            'source'      => $request->query->get('source', 'course'),
            'targetId'    => $request->query->get('targetId', 0),
            'target'      => $target,
            'attachments' => $attachments
        ));
    }

    public function testSuspendAction(Request $request, $id)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);

        if (!$testpaperResult) {
            throw $this->createNotFoundException('试卷不存在!');
        }

//权限！

        if ($testpaperResult['userId'] != $this->getCurrentUser()->id) {
            throw $this->createAccessDeniedException('不可以访问其他学生的试卷哦~');
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

    public function submitTestAction(Request $request, $id)
    {
        if ($request->getMethod() == 'POST') {
            $data     = $request->request->all();
            $answers  = array_key_exists('data', $data) ? $data['data'] : array();
            $usedTime = $data['usedTime'];

            $results = $this->getTestpaperService()->submitTestpaperAnswer($id, $answers);

            $this->getTestpaperService()->updateTestpaperResult($id, $usedTime);

            return $this->createJsonResponse(true);
        }
    }

    public function finishTestAction(Request $request, $id)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);

        if (!empty($testpaperResult) && !in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return $this->createJsonResponse(true);
        }

        if ($request->getMethod() == 'POST') {
            $data     = $request->request->all();
            $answers  = array_key_exists('data', $data) ? $data['data'] : array();
            $usedTime = $data['usedTime'];
            $user     = $this->getCurrentUser();

            //提交变化的答案
            $results = $this->getTestpaperService()->submitTestpaperAnswer($id, $answers);

            //完成试卷，计算得分
            $testResults = $this->getTestpaperService()->makeTestpaperResultFinish($id);

            $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);

            $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);
            //试卷信息记录
            $this->getTestpaperService()->finishTest($id, $user['id'], $usedTime);

            $targets = $this->get('topxia.target_helper')->getTargets(array($testpaper['target']));

            $course = $this->getCourseService()->getCourse($targets[$testpaper['target']]['id']);

            if ($this->getTestpaperService()->isExistsEssay($testResults)) {
                $user = $this->getCurrentUser();

                $message = array(
                    'id'       => $testpaperResult['id'],
                    'name'     => $testpaperResult['paperName'],
                    'userId'   => $user['id'],
                    'userName' => $user['nickname'],
                    'type'     => 'perusal'
                );

                foreach ($course['teacherIds'] as $receiverId) {
                    $result = $this->getNotificationService()->notify($receiverId, 'test-paper', $message);
                }
            }

            // @todo refactor. , wellming
            $targets = $this->get('topxia.target_helper')->getTargets(array($testpaperResult['target']));

            if ($targets[$testpaperResult['target']]['type'] == 'lesson' && !empty($targets[$testpaperResult['target']]['id'])) {
                $lessons = $this->getCourseService()->findLessonsByIds(array($targets[$testpaperResult['target']]['id']));

                if (!empty($lessons[$targets[$testpaperResult['target']]['id']])) {
                    $lesson = $lessons[$targets[$testpaperResult['target']]['id']];
                    $this->getCourseService()->finishLearnLesson($lesson['courseId'], $lesson['id']);
                }
            }

            return $this->createJsonResponse(true);
        }
    }

    public function teacherCheckAction(Request $request, $id)
    {
        //身份校验?

        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        if (!$testpaper) {
            throw $this->createNotFoundException("试卷不存在");
        }

        if (!$teacherId = $this->getTestpaperService()->canTeacherCheck($testpaper['id'])) {
            throw $this->createAccessDeniedException('无权批阅试卷！');
        }

        if ($testpaperResult['status'] != 'reviewing') {
            return $this->redirect($this->generateUrl('course_manage_test_results', array('id' => $testpaperResult['id'])));
        }

        if ($request->getMethod() == 'POST') {
            $form = $request->request->all();

            $testpaperResult = $this->getTestpaperService()->makeTeacherFinishTest($id, $testpaper['id'], $teacherId, $form);

            $user = $this->getCurrentUser();

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

        $result   = $this->getTestpaperService()->showTestpaper($id, true);
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

        foreach ($testpaper['metas']['question_type_seq'] as $type) {
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
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
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

        $paperResults = $this->getTestpaperService()->findTestpaperResultsByStatusAndTestIds(
            $testpaperIds,
            'reviewing',
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
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $courses      = $this->getCourseService()->findUserTeachCourses(array('userId' => $user['id']), 0, PHP_INT_MAX, false);
        $courseIds    = ArrayToolkit::column($courses, 'id');
        $testpapers   = $this->getTestpaperService()->findAllTestpapersByTargets($courseIds);
        $testpaperIds = ArrayToolkit::column($testpapers, 'id');

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->findTestpaperResultCountByStatusAndTestIds($testpaperIds, 'finished'),
            10
        );

        $paperResults = $this->getTestpaperService()->findTestpaperResultsByStatusAndTestIds(
            $testpaperIds,
            'finished',
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
        $user = $this->getCurrentUser();

        $course = $this->getCourseService()->tryManageCourse($id);

        $testpapers = $this->getTestpaperService()->findAllTestpapersByTarget($id);

        $testpaperIds = ArrayToolkit::column($testpapers, 'id');

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->findTestpaperResultCountByStatusAndTestIds($testpaperIds, $status),
            10
        );

        $testpaperResults = $this->getTestpaperService()->findTestpaperResultsByStatusAndTestIds(
            $testpaperIds,
            $status,
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
        $user = $this->getCurrentUser()->id;

        if (empty($user)) {
            return $this->createJsonResponse(array('error' => '您尚未登录系统或登录已超时，请先登录。'));
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($id);

        if (empty($testpaper)) {
            return $this->createJsonResponse(array('error' => '试卷已删除，请联系管理员。'));
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
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
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

    protected function geUploadFileService()
    {
        return $this->getServiceKernel()->createService('File.UploadFileService');
    }

    protected function getTaskProcessor($taskType)
    {
        return TaskProcessorFactory::create($taskType);
    }
}
