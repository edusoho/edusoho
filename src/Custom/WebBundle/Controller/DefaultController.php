<?php
namespace Custom\WebBundle\Controller;

use Topxia\WebBundle\Controller\DefaultController as TopXiaDefaultController;
use Symfony\Component\HttpFoundation\Request;


class DefaultController extends TopXiaDefaultController
{
     public function indexAction ()
    {
        // $conditions = array('status' => 'published', 'type' => 'normal');
        // $courses = $this->getCourseService()->searchCourses($conditions, 'latest', 0, 12);

        // $courseSetting = $this->getSettingService()->get('course', array());

        // if (!empty($courseSetting['live_course_enabled']) && $courseSetting['live_course_enabled']) {
        //     $recentLiveCourses = $this->getRecentLiveCourses();
        // } else {
        //     $recentLiveCourses = array();
        // }

        // $categories = $this->getCategoryService()->findGroupRootCategories('course');

        $blocks = $this->getBlockService()->getContentsByCodes(array('home_top_banner'));
        
        //获取前14个标签
        $tags = $this->getTagService()->findAllTags(0, 14);
        return $this->render('CustomWebBundle:Default:index.html.twig', array(
            'tags' => $tags,
            'blocks' => $blocks,
            'consultDisplay' => true
        ));
    }

    public function nextAction(Request $request){
        $perPageCount = 14;
        $total = $this->getTagService()->getAllTagCount();
        $currentPage = $request->query->get('page');
        $maxPage = ceil($total / $perPage) ? : 1;
        $start=0;
        //保证最后一页也有14条记录
        if($currentPage==$maxPage){
            $start = $total-14;
            $nextPage = 1;
        }else{
             $start = ($currentPage - 1) * 14;
             $nextPage = $currentPage +1;
        }
        $tags = $this->getTagService()->findAllTags($start, 14);
        return $this->render('CustomWebBundle:Default:index.html.twig', array(
        'tags' => $tags,
        'page' => $nextPage
        ));
    }




    private function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }
}