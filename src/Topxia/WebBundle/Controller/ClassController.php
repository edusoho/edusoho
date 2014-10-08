<?php
namespace Topxia\WebBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class ClassController extends ClassBaseController
{
    public function showAction(Request $request, $classId)
    {
        return $this->forward('TopxiaWebBundle:ClassThread:list', array('classId' => $classId), $request->query->all());
    }

    public function headerBlockAction($class, $classNav)
    {
        $headTeacher = $this->getClassesService()->getClassHeadTeacher($class['id']);
        $user = $this->getCurrentUser();
        return $this->render('TopxiaWebBundle:Class:header-block.html.twig', array(
            'class' => $class,
            'classNav' => $classNav,
            'user' => $user,
            'headTeacher' => $headTeacher,
        ));
    }

    public function userInfoAction($class, $userId)
    {   
        $user = $this->getCurrentUser();
        if($user->isAdmin()) {
            return $this->forward('TopxiaWebBundle:Class:admin', array('class' => $class, 'userId' => $userId));
        }
        $member = $this ->getClassesService()->getMemberByUserIdAndClassId($userId, $class['id']);
        $role = strstr($member['role'],'TEACHER') ? 'teacher' : strtolower($member['role']); 
        return $this->forward('TopxiaWebBundle:Class:' . $role, array('class' => $class, 'userId' => $userId));
    }

    public function adminAction($class, $userId)
    {
        return $this->render('TopxiaWebBundle:Class:admin-block.html.twig',array(
            'class' => $class)); 
    }

    public function teacherAction($class, $userId)
    {
        $isSignedToday = $this->getSignService()->isSignedToday($userId, 'class_sign', $class['id']);
        return $this->render('TopxiaWebBundle:Class:teacher-block.html.twig',array(
            'class' => $class,
            'isSignedToday' => $isSignedToday));
    }

    public function parentAction($class, $userId)
    {
        $isSignedToday = $this->getSignService()->isSignedToday($userId, 'class_sign', $class['id']);
        return $this->render('TopxiaWebBundle:Class:parent-block.html.twig', array(
            'class' => $class,
            'isSignedToday' => $isSignedToday
            ));
    }

    public function scheduleAction($classId, $userId, $viewType)
    {
        $date = $viewType == 'today' ? date('Ymd') : date('Ymd', strtotime('+ 1 day'));
        $results = $this->getScheduleService()->findOneDaySchedules($classId, $date);

        $lessonIds = array_keys($results['lessons']); 
        $userLearns = $this->getCourseService()->findLessonLearnsByIds($userId, $lessonIds);
        $lastSchedule = array();
        $headSchedule = array();
        foreach ($results['schedules'] as $key => $schedule) {
            if(isset($userLearns[$schedule['lessonId']]) && $userLearns[$schedule['lessonId']]['status'] == 'finished') {
                $schedule['status'] = 'finished';
                $lastSchedule[] = $schedule;
            } else {
                $schedule['status'] = 'notFinished';
                $headSchedule[] = $schedule;
            }   
        }
        $newSchedules = array_merge($headSchedule, $lastSchedule);
       
        return $this->render('TopxiaWebBundle:Class:schedule-list.html.twig', array(
            'courses' => $results['courses'],
            'lessons' => $results['lessons'],
            'teachers' => $results['teachers'],
            'schedules' => $newSchedules,
            'classId' => $classId,
            'viewType' => $viewType,
            )); 
    }

    public function studentAction($class, $userId)
    {

        $user = $this->getCurrentUser();
        $classMember = $this->getClassesService()->refreashStudentRank($user['id'], $class['id']);
        $nextLearnLesson = $this->getCourseService()->getNextLearnLessonByUserId($user['id']);
        $nextCourse = array();
        $nextLesson = array();
        if($nextLearnLesson) {
            $nextCourse = $this->getCourseService()->getCourse($nextLearnLesson['courseId']);
            $nextLesson = $this->getCourseService()->getCourseLesson($nextLearnLesson['courseId'], $nextLearnLesson['lessonId']);
        }
        
        $isSignedToday = $this->getSignService()->isSignedToday($user['id'], 'class_sign', $class['id']);
        return $this->render('TopxiaWebBundle:Class:student-block.html.twig',array(
            'class' => $class,
            'user' => $user,
            'nextCourse' => $nextCourse,
            'nextLesson' => $nextLesson,
            'classMember' => $classMember,
            'isSignedToday' => $isSignedToday));
    }

    public function statusBlockAction($class)
    {
        $members = $this->getClassesService()->findClassStudentMembers($class['id']);

        $userIds = ArrayToolkit::column($members, 'userId');

        $statuses = $this->getStatusService()->findStatusesByUserIds($userIds, 0, 10);

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($statuses, 'userId'));
        
        return $this->render('TopxiaWebBundle:Class:status-block.html.twig', array(
            'statuses' => $statuses,
            'users' => $users,

        ));
    }

    protected function getStatusService()
    {
        return $this->getServiceKernel()->createService('User.StatusService');
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    private function getSignService()
    {
        return $this->getServiceKernel()->createService('Sign.SignService');
    }

    private function getScheduleService()
    {
        return $this->getServiceKernel()->createService('Schedule.ScheduleService');
    }
}