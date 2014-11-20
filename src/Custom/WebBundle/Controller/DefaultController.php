<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\DefaultController as TopXiaDefaultController;


class DefaultController extends TopXiaDefaultController
{
     public function indexAction ()
    {
        $conditions = array('status' => 'published', 'type' => 'normal');
        $courses = $this->getCourseService()->searchCourses($conditions, 'latest', 0, 12);

        $courseSetting = $this->getSettingService()->get('course', array());

        if (!empty($courseSetting['live_course_enabled']) && $courseSetting['live_course_enabled']) {
            $recentLiveCourses = $this->getRecentLiveCourses();
        } else {
            $recentLiveCourses = array();
        }

        $categories = $this->getCategoryService()->findGroupRootCategories('course');

        $blocks = $this->getBlockService()->getContentsByCodes(array('home_top_banner'));
        return $this->render('CustomWebBundle:Default:index.html.twig', array(
            'courses' => $courses,
            'categories' => $categories,
            'blocks' => $blocks,
            'recentLiveCourses' => $recentLiveCourses,
            'consultDisplay' => true
        ));
    }
}