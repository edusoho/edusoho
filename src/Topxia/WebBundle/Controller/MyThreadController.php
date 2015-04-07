<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MyThreadController extends BaseController
{

    public function discussionsAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $conditions = array(
            'userId'=>$user['id'],
            'type'=>'discussion'
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );

        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($threads, 'courseId'));
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'latestPostUserId'));

        return $this->render('TopxiaWebBundle:MyThread:discussions.html.twig',array(
            'threadType' => 'course',
            'courses'=>$courses,
            'users'=>$users,
            'threads'=>$threads,
            'paginator' => $paginator));
    }

    public function classroomDiscussionsAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $conditions = array(
            'userId'=>$user['id'],
            'type'=>'discussion'
        );

        $paginator = new Paginator(
            $request,
            $this->getClassroomThreadService()->searchThreadCount($conditions),
            20
        );
        $threads = $this->getClassroomThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'userId'));
        $classrooms = $this->getClassroomService()->findClassroomsByIds(ArrayToolkit::column($threads, 'targetId'));

        return $this->render('TopxiaWebBundle:MyThread:classroom-discussions.html.twig',array(
            'threadType' => 'classroom',
            'paginator' => $paginator,
            'threads' => $threads,
            'users'=> $users,
            'classrooms' => $classrooms,
            ));
    }

    public function questionsAction(Request $request)
    {
        $user = $this->getCurrentUser();

        $conditions = array(
            'userId' => $user['id'],
            'type' => 'question',
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount($conditions),
            20
        );

        $threads = $this->getThreadService()->searchThreads(
            $conditions,
            'createdNotStick',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($threads, 'courseId'));
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($threads, 'latestPostUserId'));

        return $this->render('TopxiaWebBundle:MyThread:questions.html.twig',array(
            'courses'=>$courses,
            'users'=>$users,
            'threads'=>$threads,
            'paginator' => $paginator));
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getClassroomThreadService()
    {
        return $this->getServiceKernel()->createService('Thread.ThreadService');
    }

    protected function getClassroomService()
    {
        return $this->getServiceKernel()->createService('Classroom:Classroom.ClassroomService');
    }

}