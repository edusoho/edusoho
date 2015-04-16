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

        $currentUser = $this->getCurrentUser();

        $keywords = $request->query->get('q');
        $keywords=trim($keywords);
        
        $vip = $this->getAppService()->findInstallApp('Vip');

        $isShowVipSearch = $vip && version_compare($vip['version'], "1.0.7", ">=");
        
        $currentUserVipLevel = "";
        $vipLevelIds = "";
        if($isShowVipSearch){
            $currentUserVip = $this->getVipService()->getMemberByUserId($currentUser['id']);
            $currentUserVipLevel = $this->getLevelService()->getLevel($currentUserVip['levelId']);
            $vipLevels = $this->getLevelService()->findAllLevelsLessThanSeq($currentUserVipLevel['seq']);
            $vipLevelIds = ArrayToolkit::column($vipLevels, "id");
        }

        $parentId = 0;
        $categories = $this->getCategoryService()->findAllCategoriesByParentId($parentId);
        
        $categoryIds=array();
        for($i=0;$i<count($categories);$i++){
            $id = $categories[$i]['id'];
            $name = $categories[$i]['name'];
            $categoryIds[$id] = $name;
        }

        $categoryId = $request->query->get('categoryIds');
        $coursesTypeChoices = $request->query->get('coursesTypeChoices');       

        if (!$keywords) {
            goto response;
        }

        $conditions = array(
            'status' => 'published',
            'title' => $keywords,
            'categoryId' => $categoryId
        );
        if ($coursesTypeChoices == 'vipCourses') {
            $conditions['vipLevelIds'] = $vipLevelIds;
        } else if ($coursesTypeChoices == 'liveCourses') {
            $conditions['type'] = 'live';
        } else if ($coursesTypeChoices == 'freeCourses'){
            $conditions['price'] = '0.00';
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


        response:
        return $this->render('TopxiaWebBundle:Search:index.html.twig', array(
            'courses' => $courses,
            'paginator' => $paginator,
            'keywords' => $keywords,
            'isShowVipSearch' => $isShowVipSearch,
            'currentUserVipLevel' => $currentUserVipLevel,
            'categoryIds' => $categoryIds,
            'coursesTypeChoices' => $coursesTypeChoices
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