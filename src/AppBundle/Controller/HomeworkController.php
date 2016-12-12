<?php
namespace AppBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Topxia\Service\Common\ServiceKernel;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Exception\ResourceNotFoundException;

class HomeworkController extends BaseController
{
    public function startDoAction(Request $request, $homeworkId)
    {
        $homework = $this->getTestpaperService()->getTestpaper($homeworkId);
        if (empty($homework)) {
            throw new ResourceNotFoundException('homework', $homeworkId);
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($homework['courseId']);

        $result = $this->getTestpaperService()->startTestpaper($homeworkId, $homework['lessonId']);

        if ($result['status'] == 'doing') {
            return $this->redirect($this->generateUrl('homework_show', array(
                'resultId' => $result['id']
            )));
        } else {
            return $this->redirect($this->generateUrl('homework_result_show', array(
                'resultId' => $result['id']
            )));
        }
    }

    public function doTestAction(Request $request, $resultId)
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);
        if (!$result) {
            throw new ResourceNotFoundException('homeworkResult', $resultId);
        }

        list($course, $member) = $this->getCourseService()->tryTakeCourse($result['courseId']);

        $homework = $this->getTestpaperService()->getTestpaper($result['testId']);
        if (!$homework) {
            throw new ResourceNotFoundException('homework', $result['testId']);
        }

        $questions = $this->getTestpaperService()->showTestpaperItems($homework['id'], $result['id']);

        $activity = $this->getActivityService()->getActivity($result['lessonId']);

        return $this->render('WebBundle:Homework:do.html.twig', array(
            'paper'       => $homework,
            'questions'   => $questions,
            'course'      => $course,
            'paperResult' => $result,
            'activity'    => $activity,
            'showTypeBar' => 0,
            'showHeader'  => 0
        ));
    }

    public function showResultAction(Request $request, $resultId)
    {
        $homeworkResult = $this->getTestpaperService()->getTestpaperResult($resultId);

        $homework = $this->getTestpaperService()->getTestpaper($homeworkResult['testId']);

        if (!$homework) {
            throw $this->createResourceNotFoundException('homework', $homeworkResult['testId']);
        }

        if (in_array($homeworkResult['status'], array('doing', 'paused'))) {
            return $this->redirect($this->generateUrl('course_manage_show_test', array('id' => $testpaperResult['id'])));
        }

        $canLookHomework = $this->getTestpaperService()->canLookTestpaper($homeworkResult['id']);

        if (!$canLookHomework) {
            throw new AccessDeniedException($this->getServiceKernel()->trans('无权查看作业！'));
        }

        $builder   = $this->getTestpaperService()->getTestpaperBuilder($homework['type']);
        $questions = $builder->showTestItems($homework['id'], $homeworkResult['id']);

        $student = $this->getUserService()->getUser($homeworkResult['userId']);

        $attachments = $this->getTestpaperService()->findAttachments($homework['id']);
        return $this->render('WebBundle:Homework:do.html.twig', array(
            'questions'   => $questions,
            'paper'       => $homework,
            'paperResult' => $homeworkResult,
            'student'     => $student,
            'attachments' => $attachments
        ));
    }

    public function submitAction(Request $request, $resultId)
    {
        $result = $this->getTestpaperService()->getTestpaperResult($resultId);

        if (!empty($result) && !in_array($result['status'], array('doing', 'paused'))) {
            return $this->createJsonResponse(array('result' => false, 'message' => '作业已提交，不能再修改答案！'));
        }

        if ($request->getMethod() == 'POST') {
            $formData = $request->request->all();

            $paperResult = $this->getTestpaperService()->finishTest($result['id'], $formData);

            return $this->createJsonResponse(array('result' => true, 'message' => ''));
        }
    }

    public function checkListAction(Request $request, $courseId)
    {
        $course = $this->getCourseService()->tryManageCourse($courseId);

        $status = $request->query->get('status', 'all');
        if (!in_array($status, array('all', 'finished', 'reviewing', 'doing'))) {
            $status = 'all';
        }

        $conditions = array('courseId' => $courseId);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getHomeworkService()->searchHomeworkCount($conditions)
            , 10
        );

        $homeworks = $this->getHomeworkService()->searchHomeworks(
            $conditions,
            array('createdTime', 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $lessonIds = ArrayToolkit::column($homeworks, 'lessonId');
        $lessons   = $this->getCourseService()->findLessonsByIds($lessonIds);

        foreach ($homeworks as $key => $homework) {
            $homeworks[$key]['name'] = $lessons[$homework['lessonId']] ? '课时《'.$lessons[$homework['lessonId']]['title'].'》的作业' : '课时作业';
        }

        $user      = $this->getCurrentUser();
        $isTeacher = $this->getCourseService()->hasTeacherRole($course['id'], $user['id']) || $user->isSuperAdmin();

        return $this->render('HomeworkBundle:CourseHomework:check-list.html.twig', array(
            'status'    => $status,
            'homeworks' => $homeworks,
            'course'    => $course,
            'lessons'   => $lessons,
            'paginator' => $paginator,
            'isTeacher' => $isTeacher
        ));
    }

    public function resultListAction(Request $request, $id, $homeworkId)
    {
        $course = $this->getCourseService()->tryManageCourse($id);
        $user   = $this->getCurrentUser();

        $homework = $this->getHomeworkService()->getHomework($homeworkId);
        if (empty($homework)) {
            throw $this->createNotFoundException();
        }

        $status  = $request->query->get('status', 'finished');
        $keyword = $request->query->get('keyword', '');

        if (!in_array($status, array('all', 'finished', 'reviewing', 'doing'))) {
            $status = 'all';
        }

        $conditions = array('homeworkId' => $homework['id']);
        if ($status != 'all') {
            $conditions['status'] = $status;
        }

        if (!empty($keyword)) {
            $searchUser           = $this->getUserService()->getUserByNickname($keyword);
            $conditions['userId'] = $searchUser ? $searchUser['id'] : '-1';
        }

        $paginator = new Paginator(
            $request,
            $this->getHomeworkService()->searchResultsCount($conditions),
            10
        );

        $HomeworkResults = $this->getHomeworkService()->searchResults(
            $conditions,
            $status,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = array_merge($course['teacherIds'], ArrayToolkit::column($HomeworkResults, 'userId'));
        $users   = $this->getUserService()->findUsersByIds($userIds);

        return $this->render('HomeworkBundle:CourseHomework:homework-result-list.html.twig', array(
            'course'       => $course,
            'homework'     => $homework,
            'status'       => $status,
            'paperResults' => $HomeworkResults,
            'paginator'    => $paginator,
            'users'        => $users,
            'isTeacher'    => $this->getCourseService()->hasTeacherRole($id, $user['id']) || $user->isSuperAdmin()
        ));
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getQuestionService()
    {
        return $this->createService('Question:QuestionService');
    }

    protected function getActivityService()
    {
        return $this->createService('Activity:ActivityService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getServiceKernel()
    {
        return ServiceKernel::instance();
    }
}
