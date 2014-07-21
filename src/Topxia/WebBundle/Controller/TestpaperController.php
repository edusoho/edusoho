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

        $targets = $this->get('topxia.target_helper')->getTargets(array($testpaper['target']));

        if ($targets[$testpaper['target']]['type'] != 'course') {
            throw $this->createAccessDeniedException('试卷只能属于课程');
        }

        $course = $this->getCourseService()->getCourse($targets[$testpaper['target']]['id']);

        if (empty($course)) {
            return $this->createMessageResponse('info', '试卷所属课程不存在！');
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            return $this->createMessageResponse('info', '不是试卷所属课程老师或学生');
        }

        if (empty($testpaper)) {
            throw $this->createNotFoundException();
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

    public function reDoTestpaperAction (Request $request, $testId)
    {
        $targetType = $request->query->get('targetType');
        $targetId = $request->query->get('targetId');

        $userId = $this->getCurrentUser()->id;

        $testpaper = $this->getTestpaperService()->getTestpaper($testId);

        $targets = $this->get('topxia.target_helper')->getTargets(array($testpaper['target']));

        if ($targets[$testpaper['target']]['type'] != 'course') {
            throw $this->createAccessDeniedException('试卷只能属于课程');
        }

        $course = $this->getCourseService()->getCourse($targets[$testpaper['target']]['id']);

        if (empty($course)) {
            return $this->createMessageResponse('info', '试卷所属课程不存在！');
        }

        if (!$this->getCourseService()->canTakeCourse($course)) {
            return $this->createMessageResponse('info', '不是试卷所属课程老师或学生');
        }

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
            throw $this->createAccessDeniedException('无权预览试卷！');
        }

        $items = $this->getTestpaperService()->previewTestpaper($testId);

        $total = $this->makeTestpaperTotal($testpaper, $items);

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

        $total = $this->makeTestpaperTotal($testpaper, $items);

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

        if (in_array($testpaperResult['status'], array('doing', 'paused'))){
            return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testpaperResult['id'])));
        }

        $testpaper = $this->getTestpaperService()->getTestpaper($testpaperResult['testId']);

        $targets = $this->get('topxia.target_helper')->getTargets(array($testpaper['target']));
       
        if ($testpaperResult['userId'] != $this->getCurrentUser()->id){
            $course = $this->getCourseService()->tryManageCourse($targets[$testpaper['target']]['id']);
        }

        if (empty($course) and $testpaperResult['userId'] != $this->getCurrentUser()->id) {
            throw $this->createAccessDeniedException('不可以访问其他学生的试卷哦~');
        }

        $result = $this->getTestpaperService()->showTestpaper($id, true);
        $items = $result['formatItems'];
        $accuracy = $result['accuracy'];

        $total = $this->makeTestpaperTotal($testpaper, $items);

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

    public function testSuspendAction (Request $request, $id)
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
            $data = $request->request->all();
            $answers = array_key_exists('data', $data) ? $data['data'] : array();
            $usedTime = $data['usedTime'];

            $results = $this->getTestpaperService()->submitTestpaperAnswer($id, $answers);

            $this->getTestpaperService()->updateTestpaperResult($id, $usedTime);

            return $this->createJsonResponse(true);
        }

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

        $testpaperResult = $this->getTestpaperService()->getTestpaperResult($id);

        if (!empty($testpaperResult) and !in_array($testpaperResult['status'], array('doing', 'paused'))) {
            return $this->createJsonResponse(true);
        }

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

            $targets = $this->get('topxia.target_helper')->getTargets(array($testpaper['target']));

            $course = $this->getCourseService()->getCourse($targets[$testpaper['target']]['id']);

            if ($this->getTestpaperService()->isExistsEssay($testResults)) {
                $user = $this->getCurrentUser();

                $userUrl = $this->generateUrl('user_show', array('id'=>$user['id']), true);
                $teacherCheckUrl = $this->generateUrl('course_manage_test_teacher_check', array('id'=>$testpaperResult['id']), true);

                foreach ($course['teacherIds'] as $receiverId) {
                    $result = $this->getNotificationService()->notify($receiverId, 'default', "【试卷已完成】 <a href='{$userUrl}' target='_blank'>{$user['nickname']}</a> 刚刚完成了 {$testpaperResult['paperName']} ，<a href='{$teacherCheckUrl}' target='_blank'>请点击批阅</a>");
                }
            }

            // @todo refactor. , wellming
            $targets = $this->get('topxia.target_helper')->getTargets(array($testpaperResult['target']));

            if ($targets[$testpaperResult['target']]['type'] == 'lesson' and !empty($targets[$testpaperResult['target']]['id'])) {
                $lessons = $this->getCourseService()->findLessonsByIds(array($targets[$testpaperResult['target']]['id']));
                if (!empty($lessons[$targets[$testpaperResult['target']]['id']])) {
                    $lesson = $lessons[$targets[$testpaperResult['target']]['id']];
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
            throw $this->createAccessDeniedException('无权批阅试卷！');
        }

        if ($testpaperResult['status'] != 'reviewing') {
            return $this->redirect($this->generateUrl('course_manage_test_results', array('id' => $testpaperResult['id'])));
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

        $total = $this->makeTestpaperTotal($testpaper, $items);

        $types =array();
        if (in_array('essay', $testpaper['metas']['question_type_seq'])){
            array_push($types, 'essay');
        }
        if (in_array('material', $testpaper['metas']['question_type_seq'])){
            
            foreach ($items['material'] as $key => $item) {

                $questionTypes = ArrayToolkit::index(empty($item['items']) ? array() : $item['items'], 'questionType');

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


    private function makeTestpaperTotal ($testpaper, $items)
    {
        $total = array();
        foreach ($testpaper['metas']['question_type_seq'] as $type) {
            if (empty($items[$type])) {
                $total[$type]['score'] = 0;
                $total[$type]['number'] = 0;
                $total[$type]['missScore'] = 0;
            } else {
                $total[$type]['score'] = array_sum(ArrayToolkit::column($items[$type], 'score'));
                $total[$type]['number'] = count($items[$type]);
                if (array_key_exists('missScore', $testpaper['metas']) and array_key_exists($type, $testpaper["metas"]["missScore"])){
                    $total[$type]['missScore'] =  $testpaper["metas"]["missScore"][$type];
                } else {
                    $total[$type]['missScore'] = 0;
                }
            }
        }

        return $total;
    }





    public function listReviewingTestAction (Request $request)
    {
        $user = $this->getCurrentUser();
        
        if(!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

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
            'status' => $status,
            'testpapers' => ArrayToolkit::index($testpapers, 'id'),
            'paperResults' => ArrayToolkit::index($testpaperResults, 'id'),
            'course' => $course,
            'users' => $users,
            'teachers' => ArrayToolkit::index($teachers, 'id'),
            'paginator' => $paginator
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

        $testResult = $this->getTestpaperService()->findTestpaperResultByTestpaperIdAndUserIdAndActive( $id, $user);

        if (empty($testResult)) {
            return $this->createJsonResponse(array('status' => 'nodo'));
        }

        return $this->createJsonResponse(array('status' => $testResult['status'], 'resultId' => $testResult['id']));
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

}