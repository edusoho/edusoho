<?php
namespace AppBundle\Controller;

use Biz\Classroom\Service\ClassroomService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\CourseSetService;
use Biz\Course\Service\ThreadService;
use Biz\OpenCourse\Service\OpenCourseService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;

class MyTeachingController extends BaseController
{
    public function courseSetsAction(Request $request, $filter='normal')
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $conditions = array(
            'type' => 'normal'
        );

        if($filter == 'live'){
            $conditions['type'] = 'live';
        }

        $paginator = new Paginator(
            $this->get('request'),
            $this->getCourseSetService()->countUserTeachingCourseSets($user['id'], $conditions),
            20
        );

        $sets = $this->getCourseSetService()->searchUserTeachingCourseSets(
            $user['id'],
            $conditions,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $service = $this->getCourseService();
        $sets = array_map(function ($set) use ($user, $service) {
            $set['canManage'] = $set['creator'] == $user['id'];
            $set['courses'] = $service->findUserTeachingCoursesByCourseSetId($set['id'], false);
            return $set;
        }, $sets);

        return $this->render('my-teaching/teaching.html.twig', array(
            'courseSets'=> $sets,
            'paginator' => $paginator,
            'filter'    => $filter
        ));
    }

    public function openCoursesAction(Request $request, $filter)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是教师，不能查看此页面! ');
        }

        $conditions = $this->_createSearchConitons($filter);

        $paginator = new Paginator(
            $this->get('request'),
            $this->getOpenCourseService()->countCourses($conditions),
            10
        );

        $openCourses = $this->getOpenCourseService()->searchCourses(
            $conditions,
            array('createdTime' => 'DESC'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('my-teaching/open-course.html.twig', array(
            'courses'   => $openCourses,
            'paginator' => $paginator,
            'filter'    => $filter
        ));
    }

    public function classroomsAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $classrooms   = $this->getClassroomService()->searchMembers(array('role' => 'teacher', 'userId' => $user->getId()), array('createdTime', 'desc'), 0, PHP_INT_MAX);
        $classrooms   = array_merge($classrooms, $this->getClassroomService()->searchMembers(array('role' => 'assistant', 'userId' => $user->getId()), array('createdTime', 'desc'), 0, PHP_INT_MAX));
        $classroomIds = ArrayToolkit::column($classrooms, 'classroomId');

        $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);

        $members = $this->getClassroomService()->findMembersByUserIdAndClassroomIds($user->id, $classroomIds);

        foreach ($classrooms as $key => $classroom) {
            $courses      = $this->getClassroomService()->findActiveCoursesByClassroomId($classroom['id']);
            $courseIds    = ArrayToolkit::column($courses, 'id');
            $coursesCount = count($courses);

            $classrooms[$key]['coursesCount'] = $coursesCount;

            $studentCount = $this->getClassroomService()->searchMemberCount(array('role' => 'student', 'classroomId' => $classroom['id'], 'startTimeGreaterThan' => strtotime(date('Y-m-d'))));
            $auditorCount = $this->getClassroomService()->searchMemberCount(array('role' => 'auditor', 'classroomId' => $classroom['id'], 'startTimeGreaterThan' => strtotime(date('Y-m-d'))));

            $allCount = $studentCount + $auditorCount;

            $classrooms[$key]['allCount'] = $allCount;

            $todayTimeStart         = strtotime(date("Y-m-d", time()));
            $todayTimeEnd           = strtotime(date("Y-m-d", time() + 24 * 3600));
            $todayFinishedLessonNum = $this->getCourseService()->searchLearnCount(array("targetType" => "classroom", "courseIds" => $courseIds, "startTime" => $todayTimeStart, "endTime" => $todayTimeEnd, "status" => "finished"));

            $threadCount = $this->getThreadService()->searchThreadCount(array('targetType' => 'classroom', 'targetId' => $classroom['id'], 'type' => 'discussion', "startTime" => $todayTimeStart, "endTime" => $todayTimeEnd, "status" => "open"));

            $classrooms[$key]['threadCount'] = $threadCount;

            $classrooms[$key]['todayFinishedLessonNum'] = $todayFinishedLessonNum;
        }

        return $this->render('my-teaching/classroom.html.twig', array(
            'classrooms' => $classrooms,
            'members'    => $members
        ));
    }

    public function threadsAction(Request $request, $type)
    {
        $user = $this->getCurrentUser();

        if (!$user->isTeacher()) {
            return $this->createMessageResponse('error', '您不是老师，不能查看此页面！');
        }

        $myTeachingCourseCount = $this->getCourseService()->findUserTeachCourseCount(array('userId' => $user['id']), true);

        if (empty($myTeachingCourseCount)) {
            return $this->render('my-teaching/threads.html.twig', array(
                'type'       => $type,
                'threadType' => 'course',
                'threads'    => array()
            ));
        }

        $myTeachingCourses = $this->getCourseService()->findUserTeachCourses(array('userId' => $user['id']), 0, $myTeachingCourseCount, true);

        $conditions = array(
            'courseIds' => ArrayToolkit::column($myTeachingCourses, 'id'),
            'type'      => $type
        );

        $paginator = new Paginator(
            $request,
            $this->getCourseThreadService()->searchThreadCountInCourseIds($conditions),
            20
        );

        $threads = $this->getCourseThreadService()->searchThreadInCourseIds(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users   = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'latestPostUserId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($threads, 'courseId'));
        $lessons = $this->getCourseService()->findLessonsByIds(ArrayToolkit::column($threads, 'lessonId'));

        return $this->render('my-teaching/threads.html.twig', array(
            'paginator'  => $paginator,
            'threads'    => $threads,
            'users'      => $users,
            'courses'    => $courses,
            'lessons'    => $lessons,
            'type'       => $type,
            'threadType' => 'course'
        ));
    }

    private function _createSearchConitons($filter)
    {
        $user = $this->getCurrentUser();

        $conditions = array(
            'type' => $filter
        );

        if ($user->isAdmin()) {
            $conditions['userId'] = $user['id'];
        } else {
            $conditions['courseIds'] = array(-1);
            $members                 = $this->getOpenCourseService()->searchMembers(
                array('userId' => $user['id'], 'role' => 'teacher'),
                array('createdTime', 'ASC'),
                0,
                999
            );

            if ($members) {
                foreach ($members as $key => $member) {
                    $conditions['courseIds'][] = $member['courseId'];
                }
            }
        }

        return $conditions;
    }

    /**
     * @return ThreadService
     */
    protected function getCourseThreadService()
    {
        return $this->getBiz()->service('Course:ThreadService');
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    /**
     * @return \Biz\Thread\Service\ThreadService
     */
    protected function getThreadService()
    {
        return $this->getBiz()->service('Thread:ThreadService');
    }

    /**
     * @return OpenCourseService
     */
    protected function getOpenCourseService()
    {
        return $this->getBiz()->service('OpenCourse:OpenCourseService');
    }

    /**
     * @return CourseSetService
     */
    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }
}
