<?php

namespace Topxia\WebBundle\Controller;
use Topxia\Common\ArrayToolkit;

class DefaultController extends BaseController
{

    public function indexAction ()
    {
        // return $this->redirect($this->generateUrl('course_explore'));
        $template = ucfirst($this->setting('site.homepage_template', 'less'));

        return $this->forward("TopxiaWebBundle:Default:index{$template}");
    }

    public function indexLessAction()
    {
        $conditions = array('status' => 'published');
        $courses = $this->getCourseService()->searchCourses($conditions, 'latest', 0, 100);

        $userIds = array();
        foreach ($courses as $course) {
            $userIds = array_merge($userIds, $course['teacherIds']);
        }
        $users = $this->getUserService()->findUsersByIds($userIds);

        // var_dump($users);

        return $this->render('TopxiaWebBundle:Default:index-less.html.twig', array(
            'courses' => $courses,
            'users' => $users,
        ));
    }

    public function latestReviewsBlockAction($number)
    {
        $reviews = $this->getReviewService()->searchReviews(array(), 'latest', 0, $number);
        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($reviews, 'userId'));
        $courses = $this->getCourseService()->findCoursesByIds(ArrayToolkit::column($reviews, 'courseId'));


        return $this->render('TopxiaWebBundle:Default:latest-reviews-block.html.twig', array(
            'reviews' => $reviews,
            'users' => $users,
            'courses' => $courses,
        ));


    }

    public function indexMoreAction()
    {
        return $this->render('TopxiaWebBundle:Default:index-more.html.twig', array(
        ));
    }

    public function topNavigationAction()
    {
    	$navigations = $this->getNavigationService()->findNavigationsByType('top', 0, 100);

    	return $this->render('TopxiaWebBundle:Default:top-navigation.html.twig', array(
    		'navigations' => $navigations,
		));
    }

    public function footNavigationAction()
    {
        $navigations = $this->getNavigationService()->findNavigationsByType('foot', 0, 100);

        return $this->render('TopxiaWebBundle:Default:foot-navigation.html.twig', array(
            'navigations' => $navigations,
        ));
    }

    protected function getNavigationService()
    {
        return $this->getServiceKernel()->createService('Content.NavigationService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getReviewService()
    {
        return $this->getServiceKernel()->createService('Course.ReviewService');
    }

}
