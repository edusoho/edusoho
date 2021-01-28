<?php

namespace AppBundle\Controller;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use Biz\Classroom\Service\ClassroomService;
use Biz\CloudPlatform\Service\AppService;
use Biz\Course\Service\CourseService;
use Biz\Course\Service\ThreadService;
use Biz\Search\Service\SearchService;
use Biz\System\Service\SettingService;
use Biz\Taxonomy\Service\CategoryService;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\HttpFoundation\Request;
use VipPlugin\Biz\Vip\Service\LevelService;
use VipPlugin\Biz\Vip\Service\VipService;

class SearchController extends BaseController
{
    public function indexAction(Request $request)
    {
        $keywords = $request->query->get('q');
        $keywords = $this->filterKeyWord(trim($keywords));
        $type = $request->query->get('type', 'course');
        $page = $request->query->get('page', 1);

        $cloud_search_setting = $this->getSettingService()->get('cloud_search', array());
        $cloud_search_restore_time = $this->getSettingService()->get('_cloud_search_restore_time', 0);

        if (isset($cloud_search_setting['search_enabled']) && $cloud_search_setting['search_enabled'] && 'ok' == $cloud_search_setting['status'] && $cloud_search_restore_time < time()) {
            return $this->redirect(
                $this->generateUrl(
                    'cloud_search',
                    array(
                        'q' => $keywords,
                        'type' => $type,
                    )
                )
            );
        }

        $this->dispatchSearchEvent($keywords, $type, $page);

        if (!in_array($type, array('course', 'classroom'))) {
            $type = 'course';
        }

        return $this->forward(
            "AppBundle:Search:{$type}Search",
            array(
                'request' => $request,
            ),
            $request->query->all()
        );
    }

    public function classroomSearchAction(Request $request)
    {
        $keywords = $request->query->get('q');
        $keywords = $this->filterKeyWord(trim($keywords));
        $type = 'classroom';
        $filter = $request->query->get('filter');

        $conditions = array(
            'status' => 'published',
            'titleLike' => $keywords,
            'showable' => 1,
        );

        if ('free' == $filter) {
            $conditions['price'] = '0.00';
        }

        $count = $this->getClassroomService()->countClassrooms($conditions);

        $paginator = new Paginator(
            $this->get('request'),
            $count, 12
        );

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            array('updatedTime' => 'desc'),
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return $this->render(
            'search/index.html.twig',
            array(
                'type' => $type,
                'classrooms' => $classrooms,
                'filter' => $filter,
                'count' => $count,
                'paginator' => $paginator,
                'keywords' => $keywords,
            )
        );
    }

    public function courseSearchAction(Request $request)
    {
        $keywords = $request->query->get('q');
        $keywords = $this->filterKeyWord(trim($keywords));
        $type = 'course';
        $currentUser = $this->getCurrentUser();
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

        if ('vip' == $filter) {
            $conditions['vipLevelIds'] = $vipLevelIds;
        } elseif ('live' == $filter) {
            $conditions['type'] = 'live';
        } elseif ('free' == $filter) {
            $conditions['minCoursePrice'] = '0.00';
        }

        $conditions = $this->filterCourseConditions($conditions);

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
                'type' => $type,
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
        $pageSize = 10;
        $keywords = $request->query->get('q');
        $keywords = $this->filterKeyWord(trim($keywords));

        $type = $request->query->get('type', 'course');
        $page = $request->query->get('page', '1');

        $this->dispatchSearchEvent($keywords, $type, $page);

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

        if ('teacher' == $type) {
            $pageSize = 9;
            $conditions['type'] = 'user';
            $conditions['num'] = $pageSize;
            $conditions['filters'] = json_encode(array('role' => 'teacher'));
        } elseif ('thread' == $type) {
            $conditions['filters'] = json_encode(array('targetType' => 'group'));
        }

        try {
            list($resultSet, $counts) = $this->getSearchService()->cloudSearch($type, $conditions);
        } catch (\Exception $e) {
            return $this->redirect(
                $this->generateUrl(
                'search',
                    array(
                        'q' => $keywords,
                        'errorType' => 'cloudSearchError',
                    )
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

        if (1 == $cloudSearchType[$type]) {
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

    private function dispatchSearchEvent($keyword, $type, $page)
    {
        if (empty($keyword) || $page > 1 || !$this->getCurrentUser()->isLogin()) {
            return;
        }

        $biz = $this->getBiz();
        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = $biz['dispatcher'];
        $dispatcher->dispatch('user.search', new Event(array(
            'userId' => $this->getCurrentUser()->getId(),
            'q' => $keyword,
            'type' => $type,
            'uri' => urldecode($this->get('request')->getRequestUri()),
        )));
    }

    protected function filterCourseConditions($conditions)
    {
        if (!$this->isPluginInstalled('Reservation')) {
            $conditions['excludeTypes'] = array('reservation');
        }

        return $conditions;
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

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
