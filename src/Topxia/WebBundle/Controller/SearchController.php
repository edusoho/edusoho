<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class SearchController extends BaseController
{
    public function indexAction(Request $request)
    {
        $type = $request->query->get('t');
        $type = in_array($type, array('course', 'thread')) ? $type : 'course';

        $courses = $threads = $users = array();
        $paginator = null;

        $keywords = $request->query->get('q');
        if (!$keywords) {
            goto response;
        }

        if ($type == 'thread') {
            $conditions = array(
                'title' => $keywords,
            );

            $paginator = new Paginator(
                $this->get('request'),
                $this->getThreadService()->searchThreadCount($conditions)
                , 10
            );

            $threads = $this->getThreadService()->searchThreads(
                $conditions,
                'createdNotStick',
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );

            $courseIds = ArrayToolkit::column($threads, 'courseId');
            $courses = $this->getCourseService()->findCoursesByIds($courseIds);

            $userIds = ArrayToolkit::column($threads, 'latestPostUserId');
            $users = $this->getUserService()->findUsersByIds($userIds);

        } else {
            $conditions = array(
                'status' => 'published',
                'title' => $keywords
            );

            $paginator = new Paginator(
                $this->get('request'),
                $this->getCourseService()->searchCourseCount($conditions)
                , 10
            );

            $courses = $this->getCourseService()->searchCourses(
                $conditions,
                'latest',
                $paginator->getOffsetCount(),
                $paginator->getPerPageCount()
            );
        }

        response:
        return $this->render('TopxiaWebBundle:Search:index.html.twig', array(
            'courses' => $courses,
            'threads' => $threads,
            'users' => $users,
            'paginator' => $paginator,
            'keywords' => $keywords,
            'type' => $type,
        ));
    }

    private function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getThreadService()
    {
        return $this->getServiceKernel()->createService('Course.ThreadService');
    }


}