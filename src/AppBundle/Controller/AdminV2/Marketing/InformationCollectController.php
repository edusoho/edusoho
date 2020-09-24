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
use Biz\Classroom\Service\ClassroomService;
use Biz\Common\CommonException;

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

    public function createAction(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = $request->request->all();

            $event = $this->getEventService()->createEventWithLocations($data);
            return $this->createJsonResponse(true);
        }

        return $this->render('admin-v2/marketing/information-collect/edit/index.html.twig', []);
    }

    public function targetModalAction(Request $request, $type)
    {
        if ($request->query->get('eventId')) {
        }

        return $this->render('admin-v2/marketing/information-collect/edit/select/select-target-modal.html.twig', [
            'type' => $type,
        ]);
    }

    public function chooserAction(Request $request, $type)
    {
        list($users, $targets, $categories, $paginator) = $this->searchTargetsByTypeAndConditions($type, $request->query->all());

        return $this->render('admin-v2/marketing/information-collect/edit/chooser/index.html.twig', [
            'type' => $type,
            'users' => $users,
            'targets' => $targets,
            'categories' => $categories,
            'paginator' => $paginator,
        ]);
    }

    public function ajaxChooserAction(Request $request, $type)
    {
        list($users, $targets, $categories, $paginator) = $this->searchTargetsByTypeAndConditions($type, $request->query->all(), true);

        return $this->render(
            "admin-v2/marketing/information-collect/edit/chooser/{$type}-table.html.twig",
            array(
                'type' => $type,
                'users' => $users,
                'targets' => $targets,
                'categories' => $categories,
                'paginator' => $paginator,
            )
        );
    }

    public function selectedChooserAction(Request $request, $type)
    {
        if ($request->isMethod('POST')) {
            $selectedIds = $request->request->get('ids', []);
        } else {
            $selectedIds = $request->query->get('ids', []);
        }

        $conditions = ['ids' => empty($selectedIds) ? [-1] : $selectedIds];

        if ($type == EventService::TARGET_TYPE_COURSE) {
            list($targets, $paginator) = $this->searchCourseSets($conditions);
        } else {
            list($targets, $paginator) = $this->searchClassrooms($conditions);
        }

        $paginator->setBaseUrl($this->generateUrl('admin_v2_information_collect_chooser_selected', ['type' => $type]));
        $locations = $this->getEventService()->searchLocations(['targetIds' => $conditions['ids'], 'targetType' => $type], [], 0, count($selectedIds), ['targetId']);
        $locations = ArrayToolkit::index($locations, 'targetId');

        return $this->render(
            'admin-v2/marketing/information-collect/edit/select/selected-target-table.html.twig',
            array(
                'type' => $type,
                'targets' => ArrayToolkit::index($targets, 'id'),
                'selectedTargetIds' => $selectedIds,
                'locations' => $locations,
                'paginator' => $paginator,
                'hasRelated' => $this->getEventService()->countLocations(['targetIds' => $conditions['ids'],  'targetType' => $type]) > 0,
            )
        );
    }

    private function searchTargetsByTypeAndConditions($type, array $conditions,  $isAjax = false)
    {
        if (!in_array($type, [EventService::TARGET_TYPE_COURSE, EventService::TARGET_TYPE_CLASSROOM])) {
            $this->createNewException(CommonException::ERROR_PARAMETER());
        }

        if ($type == EventService::TARGET_TYPE_COURSE) {
            list($targets, $paginator) = $this->searchCourseSets($conditions, $isAjax);
            $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($targets, 'creator'));
        } else {
            list($targets, $paginator) = $this->searchClassrooms($conditions, $isAjax);
            $users = [];
        }

        if (!$isAjax) {
            $paginator->setBaseUrl($this->generateUrl('admin_v2_information_collect_chooser_ajax', ['type' => $type]));
        }

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($targets, 'categoryId'));

        return [$users, $targets, $categories, $paginator];
    }

    private function searchCourseSets($conditions)
    {
        $conditions['parentId'] = 0;

        $count = $this->getCourseSetService()->countCourseSets($conditions);
        $paginator = new Paginator($this->get('request'), $count, 10);

        $courseSets = $this->getCourseSetService()->searchCourseSets(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return [$courseSets, $paginator];
    }

    private function searchClassrooms($conditions)
    {
        $conditions['parentId'] = 0;
        if (isset($conditions['ids'])) {
            $conditions['classroomIds'] = $conditions['ids'];
            unset($conditions['ids']);
        }

        $count = $this->getClassroomService()->countClassrooms($conditions);
        $paginator = new Paginator($this->get('request'), $count, 10);

        $classrooms = $this->getClassroomService()->searchClassrooms(
            $conditions,
            ['createdTime' => 'DESC'],
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        return [$classrooms, $paginator];
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

    /**
     * @return ClassroomService
     */
    protected function getClassroomService()
    {
        return $this->createService('Classroom:ClassroomService');
    }
}
