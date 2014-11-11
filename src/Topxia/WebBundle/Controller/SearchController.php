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

        $currentUser = $this->getCurrentUser();
        $currentUserVipLevel = $this->getVipService()->getMemberByUserId($currentUser['id']);

        $keywords = $request->query->get('q');
        $vip = $this->getAppService()->findInstallApp($code);

        $isShowVipSearch = $vip && version_compare($vip['version'], "1.0.5", ">=");

        if (!$keywords) {
            goto response;
        }

        $categoryId = $request->query->get('categoryId');
        $userStatus = $request->query->get('userStatus');       

        if($userStatus == "vipCourses" ){
            $conditions = array(
                'status' => 'published',
                'title' => $keywords,
                'categoryId' => $categoryId,
                'vipLevelId' =>  $currentUserVipLevel['levelId']
            );
        }else{
            $conditions = array(
            'status' => 'published',
            'title' => $keywords,
            'categoryId' => $categoryId
            );
        }

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

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        response:
        return $this->render('TopxiaWebBundle:Search:index.html.twig', array(
            'courses' => $courses,
            'paginator' => $paginator,
            'keywords' => $keywords,
            'isShowVipSearch' => $isShowVipSearch,
            'currentUserVipLevel' => $currentUserVipLevel
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

     protected function getVipService()
    {
        return $this->getServiceKernel()->createService('Vip:Vip.VipService');
    }    

    private function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

}