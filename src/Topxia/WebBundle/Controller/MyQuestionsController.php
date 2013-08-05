<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class MyQuestionsController extends BaseController
{

    public function myQuestionsAction(Request $request)
    {
        $user = $this->getCurrentUser();
        $paginator = new Paginator(
            $request,
            $this->getThreadService()->searchThreadCount(array("userId"=>$user['id'],"type"=>'question')),
            5
        );

        $myQuestions = $this->getThreadService()->searchThreads(
            array("userId"=>$user['id'],"type"=>'question'),
            'created',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($myQuestions, 'courseId'));
        return $this->render('TopxiaWebBundle:MyQuestions:my-questions.html.twig',array(
            'courses'=>$courses,
            'myQuestions'=>$myQuestions,
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