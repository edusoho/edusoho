<?php
namespace Topxia\WebBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;

class SearchController extends BaseController
{
    public function indexAction(Request $request)
    {
        $courses = $paginator = null;
        $code = 'Vip';

        $keywords = $request->query->get('q');
        $vip = $this->getAppService()->findInstallApp($code);
        $vipLevel = $this->getLevelService()->getVipLevel();
        $vipLevelCount = $this->getLevelService()->getVipLevelCount();
        $vipLevelId=array();
        for($i=0;$i<$vipLevelCount;$i++){
            $seq = $vipLevel[$i]['seq'];
            $name = $vipLevel[$i]['name'];
            $vipLevelId[$seq] = $name;
        }

        if (!$keywords) {
            goto response;
        }
        $vipId = $request->query->get('vipLevelId');

        $conditions = array(
            'status' => 'published',
            'title' => $keywords,
            'vipLevelId' =>  $vipId
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
        response:
        return $this->render('TopxiaWebBundle:Search:index.html.twig', array(
            'courses' => $courses,
            'paginator' => $paginator,
            'keywords' => $keywords,
            'vip' => $vip,
            'vipLevel' => $vipLevel,
            'vipLevelId' => $vipLevelId
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

    protected function getAppService()
    {
        return $this->getServiceKernel()->createService('CloudPlatform.AppService');
    }

   protected function getLevelService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.LevelService');
    }

}