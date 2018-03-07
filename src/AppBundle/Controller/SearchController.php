<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\CloudPlatform\Service\AppService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\ThreadService;
use Biz\Search\Service\SearchService;
use Biz\System\Service\SettingService;
use Biz\Taxonomy\Service\CategoryService;
use Symfony\Component\HttpFoundation\Request;
use VipPlugin\Biz\Vip\Service\LevelService;
use VipPlugin\Biz\Vip\Service\VipService;

class SearchController extends BaseController
{
    public function indexAction(Request $request)
    {
        $courses = $paginator = null;

        $currentUser = $this->getCurrentUser();

        $keywords = $request->query->get('q');
        $keywords = $this->filterKeyWord(trim($keywords));

        $cloud_search_setting = $this->getSettingService()->get('cloud_search', array());

        if (isset($cloud_search_setting['search_enabled']) && $cloud_search_setting['search_enabled'] && $cloud_search_setting['status'] == 'ok') {
            return $this->redirect(
                $this->generateUrl(
                    'cloud_search',
                    array(
                        'q' => $keywords,
                        'type' => $request->query->get('type'),
                    )
                )
            );
        }

        $vip = $this->getAppService()->findInstallApp('Vip');

        $isShowVipSearch = $vip && version_compare($vip['version'], '1.0.7', '>=');

        $currentUserVipLevel = '';
        $vipLevelIds = '';

        if ($isShowVipSearch) {
            $currentUserVip = $this->getVipService()->getMemberByUserId($currentUser['id']);
            if (!empty($currentUserVip) && isset($currentUserVip['levelId'])) {
                $currentUserVipLevel = $this->getLevelService()->getLevel($currentUserVip['levelId']);
                $vipLevels = $this->getLevelService()->findAllLevelsLessThanSeq($currentUserVipLevel['seq']);
                $vipLevelIds = ArrayToolkit::column($vipLevels, 'id');
            }
        }

        $parentId = 0;
        $categories = $this->getCategoryService()->findAllCategoriesByParentId($parentId);

        $categoryIds = array();

        foreach ($categories as $key => $category) {
            $categoryIds[$key] = $category['name'];
        }

        $categoryId = $request->query->get('categoryIds');
        $filter = $request->query->get('filter');

        $conditions = array(
            'status' => 'published',
            'title' => $keywords,
            'categoryId' => $categoryId,
            'parentId' => 0,
        );

        if ($filter == 'vip') {
            $conditions['vipLevelIds'] = $vipLevelIds;
        } elseif ($filter == 'live') {
            $conditions['type'] = 'live';
        } elseif ($filter == 'free') {
            $conditions['minCoursePrice'] = '0.00';
        }

        $count = $this->getCourseSetService()->countCourseSets($conditions);
        $paginator = new Paginator(
            $this->get('request'),
            $count, 12
        );
        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            array('updatedTime' => 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render(
            'search/index.html.twig',
            array(
                'courseSets' => $courseSets,
                'paginator' => $paginator,
                'keywords' => $keywords,
                'isShowVipSearch' => $isShowVipSearch,
                'currentUserVipLevel' => $currentUserVipLevel,
                'categoryIds' => $categoryIds,
                'filter' => $filter,
                'count' => $count,
            )
        );
    }

    public function cloudSearchAction(Request $request)
    {
        $this->getSettingService()->set('cloud_search', array(
            'search_enabled' => 1,
            'status' => 'ok',
            'type' => array(
                'course' => 1,
                'teacher' => 1,
                'thread' => 1,
                'article' => 1,
                'classroom' => 1,
            ),
        ));
        $pageSize = 10;
        $keywords = $request->query->get('q');
        $keywords = $this->filterKeyWord(trim($keywords));

        $type = $request->query->get('type', 'course');
        $page = $request->query->get('page', '1');

        if (!$this->isTypeUseable($type)) {
            return $this->render('TwigBundle:Exception:error403.html.twig');
        }

        if (empty($keywords)) {
            return $this->render(
                'search/cloud-search-failure.html.twig',
                array(
                    'keywords' => $keywords,
                    'type' => $type,
                    'errorMessage' => '在上方搜索框输入关键词进行搜索.',
                )
            );
        }
        $conditions = array(
            'type' => $type,
            'words' => $keywords,
            'page' => $page,
        );

        if ($type == 'teacher') {
            $pageSize = 9;
            $conditions['type'] = 'user';
            $conditions['num'] = $pageSize;
            $conditions['filters'] = json_encode(array('role' => 'teacher'));
        } elseif ($type == 'thread') {
            $conditions['filters'] = json_encode(array('targetType' => 'group'));
        }

        try {
            list($resultSet, $counts) = $this->getSearchService()->cloudSearch($type, $conditions);
        } catch (\Exception $e) {
            return $this->render(
                'search/cloud-search-failure.html.twig',
                array(
                    'keywords' => $keywords,
                    'type' => $type,
                    'errorMessage' => '搜索失败，请稍后再试.',
                )
            );
        }

        $paginator = new Paginator($this->get('request'), $counts, $pageSize);

        return $this->render(
            'search/cloud-search.html.twig',
            array(
                'keywords' => $keywords,
                'type' => $type,
                'resultSet' => $resultSet,
                'counts' => $counts,
                'paginator' => $paginator,
            )
        );
    }

    protected function isTypeUseable($type)
    {
        $cloudSearchSetting = $this->getSettingService()->get('cloud_search');

        $cloudSearchType = empty($cloudSearchSetting['type']) ? array() : $cloudSearchSetting['type'];

        if (!array_key_exists($type, $cloudSearchType)) {
            return false;
        }

        if ($cloudSearchType[$type] == 1) {
            return true;
        }

        return false;
    }

    private function filterKeyWord($keyword)
    {
        $keyword = str_replace('<', '', $keyword);
        $keyword = str_replace('>', '', $keyword);
        $keyword = str_replace("'", '', $keyword);
        $keyword = str_replace('"', '', $keyword);
        $keyword = str_replace('=', '', $keyword);
        $keyword = str_replace('&', '', $keyword);
        $keyword = str_replace('/', '', $keyword);

        return $keyword;
    }

    /**
     * @return CourseService
     */
    protected function getCourseService()
    {
        return $this->getBiz()->service('Course:CourseService');
    }

    protected function getCourseSetService()
    {
        return $this->getBiz()->service('Course:CourseSetService');
    }

    /**
     * @return ThreadService
     */
    protected function getThreadService()
    {
        return $this->getBiz()->service('Course:ThreadService');
    }

    /**
     * @return AppService
     */
    protected function getAppService()
    {
        return $this->getBiz()->service('CloudPlatform:AppService');
    }

    /**
     * @return LevelService
     */
    protected function getLevelService()
    {
        return $this->getBiz()->service('VipPlugin:Vip:LevelService');
    }

    /**
     * @return VipService
     */
    protected function getVipService()
    {
        return $this->getBiz()->service('VipPlugin:Vip:VipService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->getBiz()->service('Taxonomy:CategoryService');
    }

    /**
     * @return SearchService
     */
    protected function getSearchService()
    {
        return $this->getBiz()->service('Search:SearchService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->getBiz()->service('System:SettingService');
    }
}
