<?php

namespace AppBundle\Controller;

use AppBundle\Common\Paginator;
use Biz\Common\CommonException;
use Biz\Search\Constant\CloudSearchType;
use Biz\Search\Constant\LocalSearchType;
use Biz\Search\Service\SearchService;
use Biz\Search\Strategy\ClassroomLocalSearchStrategy;
use Biz\Search\Strategy\CourseLocalSearchStrategy;
use Biz\Search\Strategy\ItemBankExerciseLocalSearchStrategy;
use Biz\Search\Strategy\LocalSearchStrategy;
use Biz\System\Service\SettingService;
use Codeages\Biz\Framework\Event\Event;
use Symfony\Component\HttpFoundation\Request;

class SearchController extends BaseController
{
    public function indexAction(Request $request)
    {
        $keywords = $request->query->get('q');
        $keywords = $this->filterKeyWord($keywords);
        $type = $request->query->get('type', LocalSearchType::COURSE);
        $page = $request->query->get('page', 1);

        if ($this->getSearchService()->isCloudSearchUsable()) {
            return $this->redirectToRoute(
                'cloud_search',
                [
                    'q' => $keywords,
                    'type' => $type,
                ]
            );
        }

        $this->dispatchSearchEvent($keywords, $type, $page);

        if (!in_array($type, [LocalSearchType::COURSE, LocalSearchType::CLASSROOM, LocalSearchType::ITEM_BANK_EXERCISE])) {
            $type = LocalSearchType::COURSE;
        }
        $filter = $request->query->get('filter');
        $searchStrategy = $this->createLocalSearchStrategy($type, $keywords, $filter);
        $paginator = new Paginator($request, $searchStrategy->count(), 12);
        $results = $searchStrategy->search($paginator->getOffsetCount(), $paginator->getPerPageCount());

        return $this->render(
            'search/index.html.twig',
            [
                'type' => $type,
                'paginator' => $paginator,
                'keywords' => $keywords,
                'filter' => $filter,
                'count' => $paginator->getItemCount(),
                'results' => $results,
            ]
        );
    }

    public function cloudSearchAction(Request $request)
    {
        if (!$this->getSearchService()->isCloudSearchUsable()) {
            return $this->redirectToRoute('search');
        }
        $pageSize = 10;
        $keywords = $request->query->get('q');
        $keywords = $this->filterKeyWord($keywords);

        $type = $request->query->get('type', CloudSearchType::COURSE);
        $page = $request->query->get('page', '1');

        $this->dispatchSearchEvent($keywords, $type, $page);

        if (!$this->isTypeUsable($type)) {
            return $this->render('TwigBundle:Exception:error403.html.twig');
        }

        if (empty($keywords)) {
            return $this->render(
                'search/cloud-search-failure.html.twig',
                [
                    'keywords' => $keywords,
                    'type' => $type,
                    'errorMessage' => 'cloud_search.error.input_empty',
                ]
            );
        }
        if (CloudSearchType::ITEM_BANK_EXERCISE == $type) {
            $searchStrategy = $this->createLocalSearchStrategy($type, $keywords, '');
            $paginator = new Paginator($request, $searchStrategy->count(), $pageSize);
            $resultSet = $searchStrategy->search($paginator->getOffsetCount(), $paginator->getPerPageCount());

            return $this->render(
                'search/cloud-search.html.twig',
                [
                    'keywords' => $keywords,
                    'type' => $type,
                    'resultSet' => $resultSet,
                    'counts' => $paginator->getItemCount(),
                    'paginator' => $paginator,
                ]
            );
        }
        $conditions = [
            'type' => $type,
            'words' => $keywords,
            'page' => $page,
        ];

        if ('teacher' == $type) {
            $pageSize = 9;
            $conditions['type'] = 'user';
            $conditions['num'] = $pageSize;
            $conditions['filters'] = json_encode(['role' => 'teacher']);
        } elseif ('thread' == $type) {
            $conditions['filters'] = json_encode(['targetType' => 'group']);
        }

        try {
            list($resultSet, $counts) = $this->getSearchService()->cloudSearch($type, $conditions);
        } catch (\Exception $e) {
            return $this->redirectToRoute(
                'search',
                [
                    'q' => $keywords,
                    'errorType' => 'cloudSearchError',
                ]
            );
        }

        $paginator = new Paginator($request, $counts, $pageSize);

        return $this->render(
            'search/cloud-search.html.twig',
            [
                'keywords' => $keywords,
                'type' => $type,
                'resultSet' => $resultSet,
                'counts' => $counts,
                'paginator' => $paginator,
            ]
        );
    }

    protected function isTypeUsable($type)
    {
        $cloudSearchSetting = $this->getSettingService()->get('cloud_search');

        $cloudSearchType = empty($cloudSearchSetting['type']) ? [] : $cloudSearchSetting['type'];

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
        return str_replace(['<', '>', "'", '"', '=', '&', '/'], '', trim($keyword));
    }

    private function dispatchSearchEvent($keyword, $type, $page)
    {
        if (empty($keyword) || $page > 1 || !$this->getCurrentUser()->isLogin()) {
            return;
        }

        $biz = $this->getBiz();
        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher = $biz['dispatcher'];
        $dispatcher->dispatch('user.search', new Event([
            'userId' => $this->getCurrentUser()->getId(),
            'q' => $keyword,
            'type' => $type,
            'uri' => urldecode($this->get('request')->getRequestUri()),
        ]));
    }

    /**
     * @return LocalSearchStrategy
     */
    private function createLocalSearchStrategy($type, $keyword, $filter)
    {
        $searchStrategies = [
            LocalSearchType::COURSE => CourseLocalSearchStrategy::class,
            LocalSearchType::CLASSROOM => ClassroomLocalSearchStrategy::class,
            LocalSearchType::ITEM_BANK_EXERCISE => ItemBankExerciseLocalSearchStrategy::class,
        ];
        if (empty($searchStrategies[$type])) {
            throw CommonException::ERROR_PARAMETER();
        }
        $searchStrategy = new $searchStrategies[$type]();
        $searchStrategy->setBiz($this->getBiz());
        $searchStrategy->buildSearchConditions($keyword, $filter);

        return $searchStrategy;
    }

    /**
     * @return SearchService
     */
    protected function getSearchService()
    {
        return $this->createService('Search:SearchService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
