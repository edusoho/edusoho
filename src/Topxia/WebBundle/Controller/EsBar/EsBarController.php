<?php
/**
 * author: retamia
 * Time: 15-7-7 上午11:12
 * description: 
 */
namespace Topxia\WebBundle\Controller\EsBar;
use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\ArrayToolkit;
use Topxia\WebBundle\Controller\BaseController;

class EsBarController extends BaseController{
    public function liveNotifyAction(Request $request,$userId)
    {
        $filters['type'] = 'live';
        $liveCourses = $this->getCourseService()->findUserLeaningCourses($userId,0,100,$filters);
        $liveLessons = array();
        if(!empty($liveCourses)){
            $conditions = array(
                'status' => 'published',
                'courseIds' => ArrayToolkit::column($liveCourses,'id'),
                'type' => 'live',
                'startTimeGreaterThan' => time()
            );
            $sort = array(
                'startTime','ASC'
            );
            $liveLessons = $this->getCourseService()->searchLessons($conditions,$sort,0,2);
        }
        return $this->render("TopxiaWebBundle:EsBar:live-notify.html.twig", array(
            'liveLessons' => $liveLessons,
        ));
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }
}