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
        $member = $this ->getClassesService()->getMemberByUserIdAndClassId($userId, $class['id']);
        $role = strstr($member['role'],'TEACHER') ? 'teacher' : strtolower($member['role']); 
        return $this->forward('TopxiaWebBundle:Class:' . $role, array('class' => $class, 'userId' => $userId));
    }

    public function teacherAction($class, $userId)
    {
        return $this->render('TopxiaWebBundle:Class:teacher-block.html.twig');
    }

    public function parentAction()
    {
        return $this->render('TopxiaWebBundle:Class:parent-block.html.twig');
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

}