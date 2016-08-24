<?php
namespace Topxia\WebBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends BaseController
{
    public function indexAction(Request $request)
    {
        $courses = $paginator = null;

        $currentUser = $this->getCurrentUser();

        $keywords = $request->query->get('q');
        $keywords = $this->filterKeyWord(trim($keywords));

        $cloud_search_setting = $this->getSettingService()->get('cloud_search');

        if ($cloud_search_setting['search_enabled'] && $cloud_search_setting['status'] == 'ok') {
            return $this->redirect($this->generateUrl('cloud_search', array(
                'q' => $keywords
            )));
        }

        $vip = $this->getAppService()->findInstallApp('Vip');

        $isShowVipSearch = $vip && version_compare($vip['version'], "1.0.7", ">=");

        $currentUserVipLevel = "";
        $vipLevelIds         = "";

        if ($isShowVipSearch) {
            $currentUserVip      = $this->getVipService()->getMemberByUserId($currentUser['id']);
            $currentUserVipLevel = $this->getLevelService()->getLevel($currentUserVip['levelId']);
            $vipLevels           = $this->getLevelService()->findAllLevelsLessThanSeq($currentUserVipLevel['seq']);
            $vipLevelIds         = ArrayToolkit::column($vipLevels, "id");
        }

        $parentId   = 0;
        $categories = $this->getCategoryService()->findAllCategoriesByParentId($parentId);

        $categoryIds = array();

        foreach ($categories as $key => $category) {
            $categoryIds[$key] = $category['name'];
        }

        $categoryId = $request->query->get('categoryIds');
        $filter     = $request->query->get('filter');

        $conditions = array(
            'status'     => 'published',
            'title'      => $keywords,
            'categoryId' => $categoryId,
            'parentId'   => 0
        );

        if ($filter == 'vip') {
            $conditions['vipLevelIds'] = $vipLevelIds;
        } elseif ($filter == 'live') {
            $conditions['type'] = 'live';
        } elseif ($filter == 'free') {
            $conditions['price'] = '0.00';
        }

        $count     = $this->getCourseService()->searchCourseCount($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $count
            , 12
        );
        $courses   = $this->getCourseService()->searchCourses(
            $conditions,
            'latest',
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render('TopxiaWebBundle:Search:index.html.twig', array(
            'courses'             => $courses,
            'paginator'           => $paginator,
            'keywords'            => $keywords,
            'isShowVipSearch'     => $isShowVipSearch,
            'currentUserVipLevel' => $currentUserVipLevel,
            'categoryIds'         => $categoryIds,
            'filter'              => $filter,
            'count'               => $count
        ));
    }

    public function cloudSearchAction(Request $request)
    {

        $pageSize = 10;
        $keywords = $request->query->get('q');
        $keywords = $this->filterKeyWord(trim($keywords));

        $type = $request->query->get('type', 'course');
        $page = $request->query->get('page', '1');

        if (empty($keywords)) {
            return $this->render('TopxiaWebBundle:Search:cloud-search-failure.html.twig', array(
                'keywords'     => $keywords,
                'type'         => $type,
                'errorMessage' => '在上方搜索框输入关键词进行搜索.'
            ));
        }
        $conditions = array(
            'type'  => $type,
            'words' => $keywords,
            'page'  => $page
        );

        if ($type == 'teacher') {
            $pageSize              = 9;
            $conditions['type']    = 'user';
            $conditions['num']     = $pageSize;
            $conditions['filters'] = json_encode(array('role' => 'teacher'));
        } elseif ($type == 'thread') {
            $conditions['filters'] = json_encode(array('targetType' => 'group'));
        }

        try {
            list($resultSet, $counts) = $this->getSearchService()->cloudSearch($type, $conditions);
        } catch (\Exception $e) {
            return $this->render('TopxiaWebBundle:Search:cloud-search-failure.html.twig', array(
                'keywords'     => $keywords,
                'type'         => $type,
                'errorMessage' => '搜索失败，请稍候再试.'
            ));
        }

        $paginator = new Paginator($this->get('request'), $counts, $pageSize);

        return $this->render('TopxiaWebBundle:Search:cloud-search.html.twig', array(
            'keywords'  => $keywords,
            'type'      => $type,
            'resultSet' => $resultSet,
            'counts'    => $counts,
            'paginator' => $paginator,
        ));
    }

    private function filterKeyWord($keyword)
    {
        $keyword = str_replace('<', '', $keyword);
        $keyword = str_replace('>', '', $keyword);
        $keyword = str_replace("'", '', $keyword);
        $keyword = str_replace("\"", '', $keyword);
        $keyword = str_replace('=', '', $keyword);
        $keyword = str_replace('&', '', $keyword);
        $keyword = str_replace('/', '', $keyword);
        return $keyword;
    }

    protected function getCourseService()
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

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }

    protected function getSearchService()
    {
        return $this->getServiceKernel()->createService('Search.SearchService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }
}
