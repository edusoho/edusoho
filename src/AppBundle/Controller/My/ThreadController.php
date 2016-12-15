<?php


namespace AppBundle\Controller\My;


use AppBundle\Controller\BaseController;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\ThreadService;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\Common\Paginator;
use Topxia\Service\Common\ServiceKernel;
use Topxia\Service\User\UserService;

class ThreadController extends BaseController
{

    public function discussionsAction(Request $request)
    {
        $user = $this->getUser();

        $conditions = array(
            'userId'=>$user['id'],
            'type'=>'discussion'
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->countThreads($conditions),
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

    public function questionsAction(Request $request)
    {
        $user = $this->getUser();

        $conditions = array(
            'userId' => $user['id'],
            'type' => 'question',
        );

        $paginator = new Paginator(
            $request,
            $this->getThreadService()->countThreads($conditions),
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

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->createService('Course:CourseService');
    }

    /**
     * @return UserService
     */
    protected function getUserService()
    {
        return ServiceKernel::instance()->createService('User.UserService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->createService('Course:ThreadService');
    }

}