<?php

namespace AppBundle\Controller\EsBar;

use AppBundle\Common\ArrayToolkit;
use Biz\Task\Service\TaskService;
use AppBundle\Controller\BaseController;
use Biz\Testpaper\Service\TestpaperService;
use Biz\User\UserException;
use Symfony\Component\HttpFoundation\Request;

class EsBarController extends BaseController
{
    public function studyCenterAction(Request $request)
    {
        return $this->render('es-bar/list-content/study-center.html.twig');
    }

    public function courseAction(Request $request)
    {
        $user = $this->getUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $conditions = array(
            'userId' => $user->id,
            'locked' => 0,
            'classroomId' => 0,
            'role' => 'student',
        );
        $sort = array('createdTime' => 'DESC');
        $members = $this->getCourseMemberService()->searchMembers($conditions, $sort, 0, 15);
        $courseIds = ArrayToolkit::column($members, 'courseId');
        $courseConditions = array(
            'courseIds' => $courseIds,
            'parentId' => 0,
        );
        $courses = $this->getCourseService()->searchCourses($courseConditions, 'default', 0, 15);
        $courses = ArrayToolkit::index($courses, 'id');
        $sortedCourses = array();

        if (!empty($courses)) {
            foreach ($members as $member) {
                if (empty($courses[$member['courseId']])) {
                    continue;
                }

                $course = $courses[$member['courseId']];

                if ($course['taskNum'] != 0) {
                    $course['percent'] = intval($member['learnedNum'] / $course['taskNum'] * 100);
                } else {
                    $course['percent'] = 0;
                }

                $sortedCourses[] = $course;
            }
        }

        return $this->render(
            'es-bar/list-content/study-place/my-course.html.twig',
            array(
                'courses' => $sortedCourses,
            )
        );
    }

    public function classroomAction(Request $request)
    {
        $user = $this->getUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $memberConditions = array(
            'userId' => $user->id,
            'locked' => 0,
            'role' => 'student',
        );
        $sort = array('createdTime' => 'DESC');

        $members = $this->getClassroomService()->searchMembers($memberConditions, $sort, 0, 15);

        $classroomIds = ArrayToolkit::column($members, 'classroomId');
        $classrooms = array();
        $sortedClassrooms = array();

        if (!empty($classroomIds)) {
            $classrooms = $this->getClassroomService()->findClassroomsByIds($classroomIds);
        }

        foreach ($members as $member) {
            if (empty($classrooms[$member['classroomId']])) {
                continue;
            }

            $classroom = $classrooms[$member['classroomId']];

            $sortedClassrooms[] = $classroom;
        }

        return $this->render(
            'es-bar/list-content/study-place/my-classroom.html.twig',
            array(
                'classrooms' => $sortedClassrooms,
            )
        );
    }

    public function notifyAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $notifications = $this->getNotificationService()->searchNotificationsByUserId($user->id, 0, 15);
        $this->getNotificationService()->clearUserNewNotificationCounter($user->id);

        return $this->render(
            'es-bar/list-content/notification/notify.html.twig',
            array(
                'notifications' => $notifications,
            )
        );
    }

    public function practiceAction(Request $request, $status)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            $this->createNewException(UserException::UN_LOGIN());
        }

        $conditions = array(
            'status' => $status,
            'userId' => $user['id'],
            'type' => 'homework',
        );
        $sort = array('updateTime' => 'DESC');
        $homeworkResults = $this->getTestpaperService()->searchTestpaperResults($conditions, $sort, 0, 10);

        $courseIds = ArrayToolkit::column($homeworkResults, 'courseId');
        $courses = $this->getCourseService()->findCoursesByIds($courseIds);
        $homeworkActivityIds = ArrayToolkit::column($homeworkResults, 'lessonId');

        $existCourseIds = ArrayToolkit::column($courses, 'id');
        $homeworkResults = array_filter(
            $homeworkResults,
            function ($homeworkResult) use ($existCourseIds) {
                return in_array($homeworkResult['courseId'], $existCourseIds);
            }
        );

        $conditions = array(
            'status' => $status,
            'userId' => $user['id'],
            'type' => 'testpaper',
        );
        $sort = array('endTime' => 'DESC');

        $testPaperResults = $this->getTestpaperService()->searchTestpaperResults($conditions, $sort, 0, 10);

        $testPaperActivityIds = ArrayToolkit::column($testPaperResults, 'lessonId');

        $activityIds = array_merge($homeworkActivityIds, $testPaperActivityIds);
        $tasks = $this->getTaskService()->findTasksByActivityIds($activityIds);

        return $this->render(
            'es-bar/list-content/practice/practice.html.twig',
            array(
                'testPaperResults' => $testPaperResults,
                'courses' => $courses,
                'tasks' => $tasks,
                'homeworkResults' => $homeworkResults,
                'status' => $status,
            )
        );
    }

    protected function getClassroomService()
    {
        return $this->getBiz()->service('Classroom:ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getNotificationService()
    {
        return $this->getBiz()->service('User:NotificationService');
    }

    /**
     * @return TestpaperService
     */
    protected function getTestpaperService()
    {
        return $this->getBiz()->service('Testpaper:TestpaperService');
    }

    protected function getCourseMemberService()
    {
        return $this->getBiz()->service('Course:MemberService');
    }

    /**
     * @return TaskService
     */
    protected function getTaskService()
    {
        return $this->getBiz()->service('Task:TaskService');
    }
}
