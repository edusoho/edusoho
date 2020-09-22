<?php

namespace AppBundle\Controller\AdminV2\Marketing;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\InformationCollect\Service\EventService;
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

        $events = $this->getEventService()->search(
            $conditions,
            ['createdTime' => 'DESC'],
            $page->getOffsetCount(),
            $page->getPerPageCount()
        );

        return $this->render('admin-v2/marketing/information-collect/list.html.twig', [
            'events' => $events,
            'collectedNum' => $this->getResultService()->countGroupByEventId(ArrayToolkit::column($events, 'id')),
            'paginator' => $page,
        ]);
    }

    public function closeAction(Request $request, $id)
    {
        $this->getEventService()->closeCollection($id);

        return $this->createJsonResponse(true);
    }

    public function openAction(Request $request, $id)
    {
        $this->getEventService()->openCollection($id);

        return $this->createJsonResponse(true);
    }

    /**
     * @return EventService
     */
    public function getEventService()
    {
        return $this->createService('InformationCollect:EventService');
    }

    /**
     * @return ResultService
     */
    public function getResultService()
    {
        return $this->createService('InformationCollect:ResultService');
    }
}
