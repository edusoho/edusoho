<?php

namespace AppBundle\Controller\AdminV2\Marketing;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\InformationCollect\InformationCollectionException;
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

    public function detailAction(Request $request, $id)
    {
        $event = $this->getEventService()->get($id);
        if (empty($event)) {
            $this->createNewException(InformationCollectionException::NOTFOUND_COLLECTION());
        }

        $conditions = $request->query->all();
        $conditions['eventId'] = $id;
        $paginator = new Paginator(
            $request,
            $this->getResultService()->count($conditions),
            1);

        $collectedData = $this->getResultService()->searchCollectedData(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($collectedData, 'userId'));
        $userProfiles = $this->getUserService()->findUserProfilesByIds(ArrayToolkit::column($users, 'id'));

        return $this->render('admin-v2/marketing/information-collect/detail.html.twig', [
            'event' => $event,
            'collectedNum' => $this->getResultService()->countGroupByEventId($id),
            'creator' => $this->getUserService()->getUser($event['creator']),
            'collectedData' => $collectedData,
            'users' => ArrayToolkit::index($users, 'id'),
            'profiles' => ArrayToolkit::index($userProfiles, 'id'),
            'labels' => $this->getEventService()->findItemsByEventId($id),
            'resultData' => $this->getResultService()->findResultDataByResultIds(ArrayToolkit::column($collectedData, 'id')),
            'paginator' => $paginator,
        ]);
    }

    /**
     * @return \Biz\User\Service\UserService
     */
    protected function getUserService()
    {
        return $this->createService('User:UserService');
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
