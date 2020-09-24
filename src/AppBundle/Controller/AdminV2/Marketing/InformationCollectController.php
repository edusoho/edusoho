<?php

namespace AppBundle\Controller\AdminV2\Marketing;

use AppBundle\Common\ArrayToolkit;
use AppBundle\Common\Paginator;
use AppBundle\Controller\AdminV2\BaseController;
use Biz\InformationCollect\Service\EventService;
use Biz\InformationCollect\Service\ResultService;
use Symfony\Component\HttpFoundation\Request;
use Biz\Course\Service\CourseSetService;
use Biz\Taxonomy\Service\CategoryService;

class InformationCollectController extends BaseController
{
    public function indexAction(Request $request)
    {
        $conditions = $request->query->all();

        $page = new Paginator(
            $request,
            $this->getEventService()->count($conditions),
            20
        );

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

    public function editAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();
            $event = $this->getEventService()->createEventWithLocations($data);
            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/marketing/information-collect/edit/index.html.twig', []);
    }

    public function targetModalAction(Request $request)
    {
        $type = $request->query->get('type', 'course');

        if ($request->query->get('eventId')) {
        }


        return $this->render('admin-v2/marketing/information-collect/edit/select-target-modal.html.twig', [
            'type' => $type,
        ]);
    }

    public function chooseCoursesAction(Request $request)
    {
        list($users, $conditions, $courseSets, $categories, $paginator) = $this->searchCourseSets($request->query->all());

        return $this->render('admin-v2/marketing/information-collect/edit/course-set-chooser/course-set-chooser.html.twig', [
            'users' => $users,
            'conditions' => $conditions,
            'courseSets' => $courseSets,
            'categories' => $categories,
            'paginator' => $paginator,
        ]);
    }

    public function ajaxChooseCoursesAction(Request $request)
    {
        $conditions = $request->request->all();

        list($users, $conditions, $courseSets, $categories, $paginator) = $this->searchCourseSets($request->query->all(), 'ajax');

        return $this->render(
            'admin-v2/marketing/information-collect/edit/course-set-chooser/course-set-chooser-table.html.twig',
            array(
                'users' => $users,
                'conditions' => $conditions,
                'courseSets' => $courseSets,
                'categories' => $categories,
                'paginator' => $paginator,
            )
        );
    }

    public function selectedCoursesAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $selectedIds = $request->request->get('ids', []);
        } else {
            $selectedIds = $request->query->get('ids', []);
        }

        $conditions = ['ids' => empty($selectedIds) ? [-1] : $selectedIds];

        $count = $this->getCourseSetService()->countCourseSets($conditions);
        $paginator = new Paginator($request, $count, 10);
        $paginator->setBaseUrl($this->generateUrl('admin_v2_information_collect_course_selected'));
        $courseSets = $this->getCourseSetService()->searchCourseSets($conditions, null,   $paginator->getOffsetCount(), $paginator->getPerPageCount());

        $locations = $this->getEventService()->searchLocations(['targetIds' => $conditions['ids'], 'targetType' => 'course'], [], 0, count($selectedIds), ['targetId']);
        $locations = ArrayToolkit::index($locations, 'targetId');

        return $this->render(
            'admin-v2/marketing/information-collect/edit/course-set-chooser/course-set-selected-table.html.twig',
            array(
                'courseSets' => ArrayToolkit::index($courseSets, 'id'),
                'selectedCourseSetIds' => $selectedIds,
                'locations' => $locations,
                'paginator' => $paginator,
                'hasRelated' => $this->getEventService()->countLocations(['targetIds' => $conditions['ids']]) > 0,
            )
        );
    }

    private function searchCourseSets($conditions, $type = '')
    {
        $conditions['parentId'] = 0;

        $count = $this->getCourseSetService()->countCourseSets($conditions);
        $paginator = new Paginator($this->get('request'), $count, 10);

        if ('ajax' != $type) {
            $paginator->setBaseUrl($this->generateUrl('admin_v2_information_collect_course_chooser_ajax'));
        }

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            null,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courseSets, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courseSets, 'creator'));

        return array($users, $conditions, $courseSets, $categories, $paginator);
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

    /**
     * @return CourseSetService
     */
    private function getCourseSetService()
    {
        return $this->createService('Course:CourseSetService');
    }

    /**
     * @return CategoryService
     */
    protected function getCategoryService()
    {
        return $this->createService('Taxonomy:CategoryService');
    }
}
