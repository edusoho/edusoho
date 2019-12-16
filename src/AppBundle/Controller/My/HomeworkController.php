<?php

namespace AppBundle\Controller\My;

use AppBundle\Common\Paginator;
use AppBundle\Common\ArrayToolkit;
use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Symfony\Component\HttpFoundation\Request;

class HomeworkController extends BaseController
{
    public function checkListAction(Request $request, $status)
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

        $conditions = array(
            'status' => $status,
            'type' => 'homework',
            'courseIds' => $courseIds,
        );

        if (!empty($courseIds) && 'courseTitle' == $keywordType) {
            $likeCourseSets = $this->getCourseSetService()->findCourseSetsLikeTitle($keyword);
            $likeCourseSetIds = ArrayToolkit::column($likeCourseSets, 'id');
            $likeCourses = $this->getCourseService()->findCoursesByCourseSetIds($likeCourseSetIds);
            $likeCourseIds = ArrayToolkit::column($likeCourses, 'id');
            $conditions['courseIds'] = array_intersect($conditions['courseIds'], $likeCourseIds);
        }

        $courses = $this->getCourseService()->findCoursesByIds(array_values($conditions['courseIds']));

        if ('nickname' == $keywordType && $keyword) {
            $searchUser = $this->getUserService()->getUserByNickname($keyword);
            $conditions['userId'] = $searchUser ? $searchUser['id'] : '-1';
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

        $courseSetIds = ArrayToolkit::column($paperResults, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);

        $testpaperIds = ArrayToolkit::column($paperResults, 'testId');
        $testpapers = $this->getTestpaperService()->findTestpapersByIds($testpaperIds);

        $activityIds = ArrayToolkit::column($paperResults, 'lessonId');
        $tasks = $this->getTaskService()->findTasksByActivityIds($activityIds);

        return $this->render('my/homework/check-list.html.twig', array(
            'paperResults' => $paperResults,
            'paginator' => $paginator,
            'courses' => $courses,
            'courseSets' => $courseSets,
            'users' => $users,
            'status' => $status,
            'testpapers' => $testpapers,
            'tasks' => $tasks,
            'keyword' => $keyword,
            'keywordType' => $keywordType,
        ));
    }

    public function listAction(Request $request, $status)
    {
        $user = $this->getUser();

        $conditions = array(
            'status' => $status,
            'type' => 'homework',
            'userId' => $user['id'],
        );

        $paginator = new Paginator(
            $request,
            $this->getTestpaperService()->searchTestpaperResultsCount($conditions),
            10
        );

        $paperResults = $this->getTestpaperService()->searchTestpaperResults(
            $conditions,
            array('updateTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courseIds = ArrayToolkit::column($paperResults, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);

        $courseSetIds = ArrayToolkit::column($paperResults, 'courseSetId');
        $courseSets = $this->getCourseSetService()->findCourseSetsByIds($courseSetIds);

        $activityIds = ArrayToolkit::column($paperResults, 'lessonId');
        $tasks = $this->getTaskService()->findTasksByActivityIds($activityIds);

        $homeworkIds = ArrayToolkit::column($paperResults, 'testId');
        $homeworks = $this->getTestpaperService()->findTestpapersByIds($homeworkIds);

        return $this->render('my/homework/my-homework-list.html.twig', array(
            'paperResults' => $paperResults,
            'paginator' => $paginator,
            'courses' => $courses,
            'courseSets' => $courseSets,
            'status' => $status,
            'homeworks' => $homeworks,
            'tasks' => $tasks,
        ));
    }

    protected function _getHomeworkDoTime($homeworkResults)
    {
        $homeworkIds = ArrayToolkit::column($homeworkResults, 'testId');
        $homeworkIdCount = array_count_values($homeworkIds);
        $time = 1;
        $homeworkId = 0;

        foreach ($homeworkResults as $key => $homeworkResult) {
            if ($homeworkId == $homeworkResult['testId']) {
                --$time;
            } else {
                $homeworkId = $homeworkResult['testId'];
                $time = $homeworkIdCount[$homeworkResult['testId']];
            }

            $homeworkResults[$key]['seq'] = $time;
        }

        return $homeworkResults;
    }

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

    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
