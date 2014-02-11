<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class TestpaperController extends BaseController
{
    public function indexAction (Request $request)
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

        $testpapersIds = ArrayToolkit::column($testpaperResults, 'testId');

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpapersIds);
        $testpapers = ArrayToolkit::index($testpapers, 'id');

        $targets = ArrayToolkit::column($testpapers, 'target');
        $courseIds = array_map(function($target){
            $course = explode('/', $target);
            $course = explode('-', $course[0]);
            return $course[1];
        }, $targets);

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        return $this->render('TopxiaWebBundle:MyQuiz:my-quiz.html.twig', array(
            'myQuizActive' => 'active',
            'user' => $user,
            'myTestpaperResults' => $testpaperResults,
            'myTestpapers' => $testpapers,
            'courses' => $courses,
            'paginator' => $paginator
        ));
    }

    public function doTestpaperAction (Request $request, $testId)
    {
        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');

        $userId = $this->getCurrentUser()->id;

        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        if (empty($testpaper)) {
            throw $this->createNotFoundException();
        }

        $testpaperResult = $this->getTestpaperService()->findTestpaperResultsByTestpaperIdAndUserId($testId, $userId);

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

    public function reDoTestpaperAction (Request $request, $testId)
    {
        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');

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

        $testResult = $this->getTestpaperService()->startTestpaper($testId, array('type' => $targetType, 'id' => $targetId));

        return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testResult['id'])));
    }

    public function previewTestAction (Request $request, $testId)
    {
        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        if (!$teacherId = $this->getTestpaperService()->canTeacherCheck($testpaper['id'])){
            throw createAccessDeniedException('无权预览试卷！');
        }

        $items = $this->getTestpaperService()->previewTestpaper($testId);

        $total = array();
        foreach ($testpaper['metas']['question_type_seq'] as $type) {
            if (empty($items[$type])) {
                $total[$type]['score'] = 0;
                $total[$type]['number'] = 0;
            } else {
                $total[$type]['score'] = array_sum(ArrayToolkit::column($items[$type], 'score'));
                $total[$type]['number'] = count($items[$type]);
            }
        }

        return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-show.html.twig', array(
            'items' => $items,
            'limitTime' => $testpaper['limitedTime'] * 60,
            'paper' => $testpaper,
            'id' => 0,
            'isPreview' => 'preview',
            'total' => $total
        ));
    }

    public function showTestAction (Request $request, $id)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);
        if (!$testpaperResult) {
            throw $this->createNotFoundException('试卷不存在!');
        }
        if ($testpaperResult['userId'] != $this->getCurrentUser()->id) {
            throw $this->createAccessDeniedException('不可以访问其他学生的试卷哦~');
        }
        if (in_array($testpaperResult['status'], array('reviewing', 'finished'))) {
            return $this->redirect($this->generateUrl('course_manage_test_results', array('id' => $testpaperResult['id'])));
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        $result = $this->getTestpaperService()->showTestpaper($id);
        $items = $result['formatItems'];

        $total = array();
        foreach ($testpaper['metas']['question_type_seq'] as $type) {
            if (empty($items[$type])) {
                $total[$type]['score'] = 0;
                $total[$type]['number'] = 0;
            } else {
                $total[$type]['score'] = array_sum(ArrayToolkit::column($items[$type], 'score'));
                $total[$type]['number'] = count($items[$type]);
            }
        }

        $favorites = $this->getQuestionService()->findAllFavoriteQuestionsByUserId($testpaperResult['userId']);

        return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-show.html.twig', array(
            'items' => $items,
            'limitTime' => $testpaperResult['limitedTime'] * 60,
            'paper' => $testpaper,
            'paperResult' => $testpaperResult,
            'favorites' => ArrayToolkit::column($favorites, 'questionId'),
            'id' => $id,
            'total' => $total
        ));
    }

    public function testResultAction (Request $request, $id)
    {
        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);
        if (!$testpaperResult) {
            throw $this->createNotFoundException('试卷不存在!');
        }
        if ($testpaperResult['userId'] != $this->getCurrentUser()->id) {
            throw $this->createAccessDeniedException('不可以访问其他学生的试卷哦~');
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        $result = $this->getTestpaperService()->showTestpaper($id, true);
        $items = $result['formatItems'];
        $accuracy = $result['accuracy'];

        $total = array();
        foreach ($testpaper['metas']['question_type_seq'] as $type) {
            if (empty($items[$type])) {
                $total[$type]['score'] = 0;
                $total[$type]['number'] = 0;
            } else {
                $total[$type]['score'] = array_sum(ArrayToolkit::column($items[$type], 'score'));
                $total[$type]['number'] = count($items[$type]);
            }
        }

        $favorites = $this->getQuestionService()->findAllFavoriteQuestionsByUserId($testpaperResult['userId']);

        $student = $this->getUserService()->getUser($testpaperResult['userId']);

        return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-result.html.twig', array(
            'items' => $items,
            'accuracy' => $accuracy,
            'paper' => $testpaper,
            'paperResult' => $testpaperResult,
            'favorites' => ArrayToolkit::column($favorites, 'questionId'),
            'id' => $id,
            'total' => $total,
            'student' => $student
        ));
    }

    public function submitTestAction (Request $request, $id)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $answers = array_key_exists('data', $data) ? $data['data'] : array();
            $usedTime = $data['usedTime'];

            $results = $this->getTestpaperService()->submitTestpaperAnswer($id, $answers);

            $this->getTestpaperService()->updateTestpaperResult($id, $usedTime);

            return $this->createJsonResponse(true);
        }
    }

    public function finishTestAction (Request $request, $id)
    {
        if ($request->getMethod() == 'POST') {
            $data = $request->request->all();
            $answers = array_key_exists('data', $data) ? $data['data'] : array();
            $usedTime = $data['usedTime'];
            $user = $this->getCurrentUser();

            //提交变化的答案
            $results = $this->getTestpaperService()->submitTestpaperAnswer($id, $answers);

            //完成试卷，计算得分
            $testResults = $this->getTestpaperService()->makeTestpaperResultFinish($id);

            $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);

            $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);
            //试卷信息记录
            $this->getTestpaperService()->finishTest($id, $user['id'], $usedTime);

            if ($this->getTestpaperService()->isExistsEssay($testResults)) {
                $user = $this->getCurrentUser();
                $course = $this->getCourseService()->getCourse($testpaper['targetId']);

                $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
                $teacherCheckUrl = $this->generateUrl('course_manage_test_teacher_check', array('id'=>$testpaperResult['id']), true);

                foreach ($course['teacherIds'] as $receiverId) {
                    $result = $this->getNotificationService()->notify($receiverId, 'default', "【试卷已完成】 <a href='{$userUrl}' target='_blank'>{$user['nickname']}</a> 刚刚完成了 {$testpaperResult['paperName']} ，<a href='{$teacherCheckUrl}' target='_blank'>请点击批阅</a>");
                }
            }

            // @todo refactor.
            if ($testpaperResult['targetType'] == 'lesson' and !empty($testpaperResult['targetId'])) {
                $lessons = $this->getCourseService()->findLessonsByIds(array($testpaperResult['targetId']));
                if (!empty($lessons[$testpaperResult['targetId']])) {
                    $lesson = $lessons[$testpaperResult['targetId']];
                    $this->getCourseService()->finishLearnLesson($lesson['courseId'], $lesson['id']);
                }
            }

            return $this->createJsonResponse(true);
        }
    }

    public function teacherCheckAction (Request $request, $id)
    {
        //身份校验?

        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);


        if (!$teacherId = $this->getTestpaperService()->canTeacherCheck($testpaper['id'])){
            throw createAccessDeniedException('无权批阅试卷！');
        }

        if ($testpaperResult['status'] != 'reviewing') {
            return $this->createMessageResponse('info', '只有待批阅状态的试卷，才能批阅！');
        }

        if ($request->getMethod() == 'POST') {
            $form = $request->request->all();

            $testpaperResult = $this->getTestpaperService()->makeTeacherFinishTest($id, $testpaper['id'], $teacherId, $form);

            $user = $this->getCurrentUser();

            $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
            $testpaperResultUrl = $this->generateUrl('course_manage_test_results', array('id'=>$testpaperResult['id']), true);

            $result = $this->getNotificationService()->notify($testpaperResult['userId'], 'default', "【试卷已批阅】 <a href='{$userUrl}' target='_blank'>{$user['nickname']}</a> 刚刚批阅了 {$testpaperResult['paperName']} ，<a href='{$testpaperResultUrl}' target='_blank'>请点击查看结果</a>");
            
            return $this->createJsonResponse(true);
        }

        $result = $this->getTestpaperService()->showTestpaper($id, true);
        $items = $result['formatItems'];
        $accuracy = $result['accuracy'];

        $total = array();
        foreach ($testpaper['metas']['question_type_seq'] as $type) {
            $total[$type]['score'] = array_sum(ArrayToolkit::column($items[$type], 'score'));
            $total[$type]['number'] = count($items[$type]);
        }

        $types =array();
        if (in_array('essay', $testpaper['metas']['question_type_seq'])){
            array_push($types, 'essay');
        }
        if (in_array('material', $testpaper['metas']['question_type_seq'])){
            
            foreach ($items['material'] as $key => $item) {

                $questionTypes = ArrayToolkit::index(empty($item['items']) ? array() : $item['items'], 'type');

                if(array_key_exists('essay', $questionTypes)){
                    array_push($types, 'material');
                }
            }
        }

        $student = $this->getUserService()->getUser($testpaperResult['userId']);

        return $this->render('TopxiaWebBundle:QuizQuestionTest:testpaper-review.html.twig', array(
            'items' => $items,
            'accuracy' => $accuracy,
            'paper' => $testpaper,
            'paperResult' => $testpaperResult,
            'id' => $id,
            'total' => $total,
            'types' => $types,
            'student' => $student
        ));
    }

    public function pauseTestAction(Request $request)
    {
        return $this->render('TopxiaWebBundle:QuizQuestionTest:do-test-pause-modal.html.twig'); 
    }







    public function listReviewingTestAction (Request $request)
    {
        $user = $this->getCurrentUser();

        $teacherTests = $this->getTestpaperService()->findTeacherTestpapersByTeacherId($user['id']);

        $testpaperIds = ArrayToolkit::column($teacherTests, 'id');

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpaperIds);

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

        $targets = ArrayToolkit::column($testpapers, 'target');
        $courseIds = array_map(function($target){
            $course = explode('/', $target);
            $course = explode('-', $course[0]);
            return $course[1];
        }, $targets);

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        return $this->render('TopxiaWebBundle:MyQuiz:teacher-test-layout.html.twig', array(
            'status' => 'reviewing',
            'users' => ArrayToolkit::index($users, 'id'),
            'paperResults' => $paperResults,
            'courses' => ArrayToolkit::index($courses, 'id'),
            'testpapers' => ArrayToolkit::index($testpapers, 'id'),
            'teacher' => $user,
            'paginator' => $paginator
        ));
    }

    public function listFinishedTestAction (Request $request)
    {
        $user = $this->getCurrentUser();


        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->findTestpaperResultCountByStatusAndTeacherIds(array($user['id']), 'finished'),
            10
        );

        $paperResults = $this->getTestpaperService()->findTestpaperResultsByStatusAndTeacherIds(
            array($user['id']),
            'finished',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );
        
        $testpaperIds = ArrayToolkit::column($paperResults, 'testId');

        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpaperIds);

        $userIds = ArrayToolkit::column($paperResults, 'userId');

        $users = $this->getUserService()->findUsersByIds($userIds);

        $targets = ArrayToolkit::column($testpapers, 'target');
        $courseIds = array_map(function($target){
            $course = explode('/', $target);
            $course = explode('-', $course[0]);
            return $course[1];
        }, $targets);

        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        return $this->render('TopxiaWebBundle:MyQuiz:teacher-test-layout.html.twig', array(
            'status' => 'finished',
            'users' => ArrayToolkit::index($users, 'id'),
            'paperResults' => $paperResults,
            'courses' => ArrayToolkit::index($courses, 'id'),
            'testpapers' => ArrayToolkit::index($testpapers, 'id'),
            'teacher' => $user,
            'paginator' => $paginator
        ));
    }

    public function teacherCheckInCourseAction (Request $request, $id, $status)
    {
        $user = $this->getCurrentUser();

        $course = $this->getCourseService()->tryManageCourse($id);

        $papers = $this->getTestpaperService()->findAllTestpapersByTarget($id);

        $paperIds = ArrayToolkit::column($papers, 'id');

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->findTestpaperResultCountByStatusAndTestIds($paperIds, $status),
            10
        );

        $paperResults = $this->getTestpaperService()->findTestpaperResultsByStatusAndTestIds(
            $paperIds,
            $status,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($paperResults, 'userId'));

        $teacherIds = ArrayToolkit::column($paperResults, 'checkTeacherId');

        $teachers = $this->getUserService()->findUsersByIds($teacherIds);


        return $this->render('TopxiaWebBundle:MyQuiz:list-course-test-paper.html.twig', array(
            'status' => $status,
            'testpapers' => ArrayToolkit::index($papers, 'id'),
            'paperResults' => ArrayToolkit::index($paperResults, 'id'),
            'course' => $course,
            'users' => $users,
            'teachers' => ArrayToolkit::index($teachers, 'id'),
            'paginator' => $paginator
        ));
    }
    
    public function openTestpaperAction (Request $request, $id)
    {
        $testpaper = $this->getTestpaperService()->publishTestpaper($id);

        $user = $this->getUserService()->getUser($testpaper['updatedUserId']);

        $target = explode('/', $testpaper['target']);
        $target = explode('-', $target[0]);

        $course = $this->getCourseService()->getCourse($target[1]);

        return $this->render('TopxiaWebBundle:QuizQuestionTest:tr.html.twig', array(
            'item' => $testpaper,
            'user' => $user,
            'course' => $course
        ));
    }

    public function closeTestpaperAction (Request $request, $id)
    {
        $testpaper = $this->getTestpaperService()->closeTestpaper($id);

        $user = $this->getUserService()->getUser($testpaper['updatedUserId']);

        $target = explode('/', $testpaper['target']);
        $target = explode('-', $target[0]);

        $course = $this->getCourseService()->getCourse($target[1]);

        return $this->render('TopxiaWebBundle:QuizQuestionTest:tr.html.twig', array(
            'item' => $testpaper,
            'user' => $user,
            'course' => $course
        ));
    }


    private function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }

    private function getQuestionService()
    {
        return $this->getServiceKernel()->createService('Question.QuestionService');
    }

    private function getCourseService ()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    private function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
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

        $testResult = $this->getTestpaperService()->findTestpaperResultByTestIdAndUserId( $id, $user);

        if (empty($testResult)) {
            return $this->createJsonResponse(array('status' => 'nodo'));
        }

        return $this->createJsonResponse(array('status' => $testResult['status'], 'resultId' => $testResult['id']));
    }

    // private function getTestpaperService()
    // {
    //     return $this -> getServiceKernel()->createService('Quiz.TestService');
    // }

}