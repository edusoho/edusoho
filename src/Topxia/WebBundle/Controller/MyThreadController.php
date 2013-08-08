<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MyThreadController extends BaseController
{

    public function myThreadsAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount(array("userId"=>$user['id'],"type"=>'discussion')),
            5
        );

        $myThreads = $this->getThreadService()->searchThreads(
            array("userId"=>$user['id'],"type"=>'discussion'),
            'created',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($myThreads, 'courseId'));
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($myThreads, 'latestPostUserId'));

        return $this->render('TopxiaWebBundle:MyThread:my-threads.html.twig',array(
            'courses'=>$courses,
            'users'=>$users,
            'myThreads'=>$myThreads,
            'paginator' => $paginator));
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }

    protected function getNoteService()
    {
        return $this->getServiceKernel()->createService('Course.NoteService');
    }

    protected function getUserService()
    {
        return $this->getServiceKernel()->createService('User.UserService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

}