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

        $course = $this->getCourseService()->tryManageCourse($id);

        $testpapers = $this->getTestpaperService()->findAllTestpapersByTarget($id);

        $testpaperIds = ArrayToolkit::column($testpapers, 'id');

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->findTestpaperResultCountByStatusAndTestIdsAndOrgId($testpaperIds, $status, $user['orgCode']),
            10
        );

        $testpaperResults = $this->getTestpaperService()->findTestpaperResultsByStatusAndTestIdsAndOrgId(
            $testpaperIds,
            $status,
            $user['orgCode'],
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

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Custom:Testpaper.TestpaperService');
    }
}