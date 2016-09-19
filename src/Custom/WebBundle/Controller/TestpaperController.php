<?php
namespace Custom\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\TestpaperController as BaseTestpaperController;

class TestpaperController extends BaseTestpaperController
{
    public function teacherCheckInCourseAction(Request $request, $id, $status)
    {
        $user = $this->getCurrentUser();
        $users = $this->getUserService()->findUsersByOrgCode($user['orgCode']);
        $userIds = ArrayToolkit::column($users, 'id');

        $course = $this->getCourseService()->tryManageCourse($id);

        $testpapers = $this->getTestpaperService()->findAllTestpapersByTarget($id);

        $testpaperIds = ArrayToolkit::column($testpapers, 'id');

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->findTestPaperResultCountByStatusAndTestIdsAndUserIds($testpaperIds, $status, $userIds),
            10
        );

        $testpaperResults = $this->getTestpaperService()->findTestPaperResultsByStatusAndTestIdsAndUserIds(
            $testpaperIds,
            $status,
            $userIds,
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
    public function listReviewingTestAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $users = $this->getUserService()->findUsersByOrgCode($user['orgCode']);
        $userIds = ArrayToolkit::column($users, 'id');

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', $this->getServiceKernel()->trans('您不是老师，不能查看此页面！'));
        }

        $courses      = $this->getCourseService()->findUserTeachCourses(array('userId' => $user['id']), 0, PHP_INT_MAX, false);
        $courseIds    = ArrayToolkit::column($courses, 'id');
        $testpapers   = $this->getTestpaperService()->findAllTestpapersByTargets($courseIds);
        $testpaperIds = ArrayToolkit::column($testpapers, 'id');

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->findTestPaperResultCountByStatusAndTestIdsAndUserIds($testpaperIds, 'reviewing', $userIds),
            10
        );

        $paperResults = $this->getTestpaperService()->findTestPaperResultsByStatusAndTestIdsAndUserIds(
            $testpaperIds,
            'reviewing',
            $userIds,
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
        $users = $this->getUserService()->findUsersByOrgCode($user['orgCode']);
        $userIds = ArrayToolkit::column($users, 'id');

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', $this->getServiceKernel()->trans('您不是老师，不能查看此页面！'));
        }

        $courses      = $this->getCourseService()->findUserTeachCourses(array('userId' => $user['id']), 0, PHP_INT_MAX, false);
        $courseIds    = ArrayToolkit::column($courses, 'id');
        $testpapers   = $this->getTestpaperService()->findAllTestpapersByTargets($courseIds);
        $testpaperIds = ArrayToolkit::column($testpapers, 'id');

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->findTestPaperResultCountByStatusAndTestIdsAndUserIds($testpaperIds, 'finished', $userIds),
            10
        );

        $paperResults = $this->getTestpaperService()->findTestPaperResultsByStatusAndTestIdsAndUserIds(
            $testpaperIds,
            'finished',
            $userIds,
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

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Custom:Testpaper.TestpaperService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('Custom:User.UserService');
    }
}