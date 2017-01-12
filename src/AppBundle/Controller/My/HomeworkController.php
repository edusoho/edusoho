<?php

namespace AppBundle\Controller\My;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;

class HomeworkController extends BaseController
{
    public function checkListAction(Request $request, $status)
    {
        $user = $this->getUser();
        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $teacherCourses = $this->getCourseMemberService()->findTeacherMembersByUserId($user['id']);
        $courseIds      = ArrayToolkit::column($teacherCourses, 'courseId');
        $courses        = $this->getCourseService()->findCoursesByIds($courseIds);

        $conditions = array(
            'status'    => $status,
            'type'      => 'homework',
            'courseIds' => $courseIds
        );

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->searchTestpaperResultsCount($conditions),
            10
        );

        $orderBy = $status == 'reviewing' ? array('endTime' => 'ASC') : array('checkedTime' => 'DESC');

        $paperResults = $this->getTestpaperService()->searchTestpaperResults(
            $conditions,
            $orderBy,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $userIds = ArrayToolkit::column($paperResults, 'userId');
        $userIds = array_merge($userIds, ArrayToolkit::column($paperResults, 'checkTeacherId'));
        $users   = $this->getUserService()->findUsersByIds($userIds);

        $courseSetIds = ArrayToolkit::column($paperResults, 'courseSetId');
        $courseSets   = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);

        $testpaperIds = ArrayToolkit::column($paperResults, 'testId');
        $testpapers   = $this->getTestpaperService()->findTestpapersByIds($testpaperIds);

        return $this->render('my/homework/check-list.html.twig', array(
            'paperResults' => $paperResults,
            'paginator'    => $paginator,
            'courses'      => $courses,
            'courseSets'   => $courseSets,
            'users'        => $users,
            'status'       => $status,
            'testpapers'   => $testpapers
        ));
    }

    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }
}
