<?php

namespace AppBundle\Controller\InformationCollect;

use ApiBundle\Api\ApiRequest;
use AppBundle\Controller\BaseController;
use Biz\InformationCollect\InformationCollectException;
use Biz\InformationCollect\Service\EventService;
use Symfony\Component\HttpFoundation\Request;

class InformationCollectController extends BaseController
{
    public function indexAction(Request $request, $eventId)
    {
        $event = $this->getEventService()->get($eventId);
        if (empty($event)) {
            throw $this->createNewException(InformationCollectException::NOTFOUND_COLLECTION());
        }

        return $this->render('information-collection/index.html.twig', ['eventId' => $eventId]);
    }

    public function eventFormAction(Request $request, $eventId)
    {
        $apiRequest = new ApiRequest(
            "/api/information_collect_form/{$eventId}",
            'GET'
        );

        $event = $this->get('api_resource_kernel')->handleApiRequest($apiRequest);

        return $this->render('information-collection/form.html.twig', [
            'event' => $event,
            'goto' => $request->query->get('goto', ''),
        ]);
    }

    /**
     * @return EventService
     */
    private function getEventService()
    {
        return $this->createService('InformationCollect:EventService');
    }
}
