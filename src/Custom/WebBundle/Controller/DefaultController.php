<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\BaseController as BaseController;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;


class DefaultController extends BaseController
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
        
        $courseCarousels=$this->getCourseCarouselService()->findAllCourseCarousels();
        $columns=$this->getColumnService()->findColumnsByIds(ArrayToolkit::column($courseCarousels,'columnId'));
        $columns=ArrayToolkit::index($columns,'id');

        return $this->render('TopxiaWebBundle:Default:index.html.twig', array(
            'courses' => $courses,
            'categories' => $categories,
            'blocks' => $blocks,
            'recentLiveCourses' => $recentLiveCourses,
            'consultDisplay' => true,
            'courseCarousels'=>$courseCarousels,
            'columns'=>$columns
        ));
    }

    protected function getCourseCarouselService()
    {
        return $this->getServiceKernel()->createService('Custom:CourseCarousel.CourseCarouselService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Custom:Taxonomy.CategoryService');
    }

    protected function getColumnService()
    {
        return $this->getServiceKernel()->createService('Custom:Taxonomy.ColumnService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getBlockService()
    {
        return $this->getServiceKernel()->createService('Content.BlockService');
    }
}