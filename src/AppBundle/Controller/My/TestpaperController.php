<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\MemberService;
use Biz\Testpaper\Service\TestpaperService;
use Symfony\Component\HttpFoundation\Request;

class TestpaperController extends BaseController
{
    public function checkListAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $status = $request->query->get('status', 'reviewing');
        $keywordType = $request->query->get('keywordType', 'nickname');
        $keyword = $request->query->get('keyword', '');

        $teacherCourses = $this->getCourseMemberService()->findTeacherMembersByUserId($user['id']);
        $courseIds = ArrayToolkit::column($teacherCourses, 'courseId');

        if (!in_array($status, array('finished', 'reviewing'))) {
            $status = 'reviewing';
        }

        $conditions = array(
            'type' => 'testpaper',
            'courseIds' => $courseIds,
            'status' => $status
        );

        if (!empty($courseIds) && 'courseTitle' == $keywordType) {
            $courseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($keyword);
            $courseSetIds = ArrayToolkit::column($courseSets, 'id');
            $courses = $this->getCourseService()->findCoursesByCourseSetIds($courseSetIds);
            $courseIds = ArrayToolkit::column($courses, 'id');
            $conditions['courseIds'] = array_intersect($conditions['courseIds'], $courseIds);
        }


        if ('nickname' == $keywordType && $keyword) {
            $searchUser = $this->getUserService()->getUserByNickname($keyword);
            $conditions['userId'] = $searchUser ? $searchUser['id'] : '-1';
        }

        if ($status == 'finished') {
            $conditions['checkTeacherId'] = $user['id'];
        }

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
        $users = $this->getUserService()->findUsersByIds($userIds);
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $courseSetIds = ArrayToolkit::column($paperResults, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);

        $testpaperIds = ArrayToolkit::column($paperResults, 'testId');
        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpaperIds);

        return $this->render('my/testpaper/check-list.html.twig', array(
            'paperResults' => $paperResults,
            'paginator' => $paginator,
            'courses' => $courses,
            'courseSets' => $courseSets,
            'users' => $users,
            'status' => $status,
            'testpapers' => $testpapers,
            'keywordType' => $keywordType,
            'keyword' => $keyword
        ));
    }

    public function listAction(Request $request)
    {
        $user = $this->getUser();
        if (!$user->isLogin()) {
            return $this->createMessageResponse('error', '请先登录！', '', 5, $this->generateUrl('login'));
        }

        $conditions = array(
            'userId' => $user['id'],
            'type' => 'testpaper',
        );

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->searchTestpaperResultsCount($conditions),
            10
        );

        $paperResults = $this->getTestpaperService()->searchTestpaperResults(
            $conditions,
            array('beginTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseSetIds = ArrayToolkit::column($paperResults, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);

        $courseIds = ArrayToolkit::column($paperResults, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $testpaperIds = ArrayToolkit::column($paperResults, 'testId');
        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpaperIds);

        $activityIds = ArrayToolkit::column($paperResults, 'lessonId');
        $tasks = $this->getTaskService()->findTasksByActivityIds($activityIds);

        return $this->render('my/testpaper/my-testpaper-list.html.twig', array(
            'paperResults' => $paperResults,
            'paginator' => $paginator,
            'courses' => $courses,
            'courseSets' => $courseSets,
            'testpapers' => $testpapers,
            'tasks' => $tasks,
            'nav' => 'testpaper',
        ));
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->createService('Testpaper:TestpaperService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return MemberService
     */
    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    protected function getUserService()
    {
        return $this->getBiz()->service('User:UserService');
    }

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
