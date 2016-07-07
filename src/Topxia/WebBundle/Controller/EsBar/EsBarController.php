<?php
namespace Topxia\WebBundle\Controller\EsBar;

use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;
use Topxia\WebBundle\Controller\BaseController;

class EsBarController extends BaseController
{

    public function studyCenterAction(Request $request)
    {
         return $this->render("TopxiaWebBundle:EsBar:ListContent/study-center.html.twig", array(
        ));
    }

    public function courseAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('用户没有登录,不能查看!');
        }

        $conditions = array(
            'userId'      => $user->id,
            'locked'      => 0,
            'classroomId' => 0,
            'role'        => 'student'
        );
        $sort             = array('createdTime', 'DESC');
        $members          = $this->getCourseService()->searchMembers($conditions, $sort, 0, 15);
        $courseIds        = ArrayToolkit::column($members, 'courseId');
        $courseConditions = array(
            'courseIds' => $courseIds,
            'parentId'  => 0
        );
        $courses       = $this->getCourseService()->searchCourses($courseConditions, 'default', 0, 15);
        $courses       = ArrayToolkit::index($courses, 'id');
        $sortedCourses = array();

        if (!empty($courses)) {
            foreach ($members as $member) {
                if (empty($courses[$member['courseId']])) {
                    continue;
                }

                $course = $courses[$member['courseId']];

                if ($course['lessonNum'] != 0) {
                    $course['percent'] = intval($member['learnedNum'] / $course['lessonNum'] * 100);
                } else {
                    $course['percent'] = 0;
                }

                $sortedCourses[] = $course;
            }
        }

        return $this->render("TopxiaWebBundle:EsBar:ListContent/StudyPlace/my-course.html.twig", array(
            'courses' => $sortedCourses
        ));
    }

    public function classroomAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('用户没有登录,不能查看!');
        }

        $memberConditions = array(
            'userId' => $user->id,
            'locked' => 0,
            'role'   => 'student'
        );
        $sort = array('createdTime', 'DESC');

        $members = $this->getClassroomService()->searchMembers($memberConditions, $sort, 0, 15);

        $classroomIds     = ArrayToolkit::column($members, 'classroomId');
        $classrooms       = array();
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

        return $this->render("TopxiaWebBundle:EsBar:ListContent/StudyPlace/my-classroom.html.twig", array(
            'classrooms' => $sortedClassrooms
        ));
    }

    public function notifyAction(Request $request)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('用户没有登录,不能查看!');
        }

        $notifications = $this->getNotificationService()->findUserNotifications(
            $user->id,
            0,
            15
        );
        $this->getNotificationService()->clearUserNewNotificationCounter($user->id);

        return $this->render('TopxiaWebBundle:EsBar:ListContent/Notification/notify.html.twig', array(
            'notifications' => $notifications
        ));
    }

    public function practiceAction(Request $request, $status)
    {
        $user = $this->getCurrentUser();

        if (!$user->isLogin()) {
            throw $this->createAccessDeniedException('用户没有登录,不能查看!');
        }

        $homeworkResults  = array();
        $testPaperResults = array();
        $courses          = array();
        $lessons          = array();

        if ($this->isPluginInstalled('Homework')) {
            $conditions = array(
                'status' => $status,
                'userId' => $user->id
            );
            $homeworkResults = $this->getHomeworkService()->searchResults(
                $conditions,
                array('updatedTime', 'DESC'),
                0,
                10
            );
            $homeworkCourseIds = ArrayToolkit::column($homeworkResults, 'courseId');
            $homeworkLessonIds = ArrayToolkit::column($homeworkResults, 'lessonId');
            $courses           = $this->getCourseService()->findCoursesByIds($homeworkCourseIds);
            $lessons           = $this->getCourseService()->findLessonsByIds($homeworkLessonIds);
        }

        $testPaperConditions = array(
            'status' => $status,
            'userId' => $user->id
        );

        $testPaperResults = $this->getTestpaperService()->searchTestpaperResults(
            $testPaperConditions,
            array('endTime', 'DESC'),
            0,
            10
        );

        return $this->render('TopxiaWebBundle:EsBar:ListContent/Practice/practice.html.twig', array(
            'testPaperResults' => $testPaperResults,
            'courses'          => $courses,
            'lessons'          => $lessons,
            'homeworkResults'  => $homeworkResults,
            'status'           => $status
        ));
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getNotificationService()
    {
        return $this->getServiceKernel()->createService('User.NotificationService');
    }

    protected function getHomeworkService()
    {
        return $this->getServiceKernel()->createService('Homework:Homework.HomeworkService');
    }

    protected function getTestpaperService()
    {
        return $this->getServiceKernel()->createService('Testpaper.TestpaperService');
    }
}
