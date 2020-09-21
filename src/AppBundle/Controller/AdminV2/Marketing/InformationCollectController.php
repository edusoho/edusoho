<?php

namespace AppBundle\Controller\AdminV2\Marketing;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\InformationCollect\Service\EventService;
use Biz\InformationCollect\Service\LocationService;
use Biz\InformationCollect\Service\ResultService;
use Symfony\Component\HttpFoundation\Request;

class InformationCollectController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        $page = new Paginator(
            $request,
            $this->getEventService()->count($conditions),
            20);

        $list = $this->getEventService()->search(
            $conditions,
            ['createdTime' => 'DESC'],
            $page->getOffsetCount(),
            $page->getPerPageCount()
        );

        return $this->render('admin-v2/marketing/information-collect/list.html.twig', [
            'lists' => $this->filterList($list),
            'paginator' => $page,
        ]);
    }

    protected function filterList($collects)
    {
        $locations = $this->getLocationService()->getCollectLocations(ArrayToolkit::column($collects, 'id'));

        $collectCounts = $this->getResultService()->countGroupByEventId(ArrayToolkit::column($collects, 'id'));

        foreach ($collects as &$collect) {
            $collect['action'] = 'admin.information_collection.action.'.$collect['action'];
            $collect['status'] = 'admin.information_collection.status.'.$collect['status'];
            $collect['location'] = isset($locations[$collect['id']]) ? $locations[$collect['id']]['targetInfo'] : '';
            $collect['collectNum'] = isset($collectCounts[$collect['id']]) ? $collectCounts[$collect['id']]['collectNum'] : 0;
        }

        return $collects;
    }

    /**
     * @return EventService
     */
    public function getEventService()
    {
        return $this->createService('InformationCollect:EventService');
    }

    /**
     * @return LocationService
     */
    public function getLocationService()
    {
        return $this->createService('InformationCollect:LocationService');
    }

    /**
     * @return ResultService
     */
    public function getResultService()
    {
        return $this->createService('InformationCollect:ResultService');
    }
}
