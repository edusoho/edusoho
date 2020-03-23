<?php

namespace AppBundle\Controller\AdminV2\Operating;

use AppBundle\Controller\AdminV2\BaseController;
use Biz\Content\Service\NavigationService;
use Biz\System\Service\SettingService;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Common\ArrayToolkit;

class NavigationController extends BaseController
{
    public function indexAction(Request $request)
    {
        $type = $request->query->get('type', 'top');

        $navigations = $this->getNavigationService()->getNavigationsListByType($type);

        return $this->render('admin-v2/operating/navigation/index.html.twig', array(
            'type' => $type,
            'navigations' => $navigations,
        ));
    }

    public function deleteAction(Request $request, $id)
    {
        $result = $this->getNavigationService()->deleteNavigation($id);
        if ($result > 0) {
            return $this->createJsonResponse(array('status' => 'ok'));
        } else {
            return $this->createJsonResponse(array('status' => 'error'));
        }
    }

    public function createAction(Request $request)
    {
        if ('POST' == $request->getMethod()) {
            $navigation = $request->request->all();
            $navigation = $this->getNavigationService()->createNavigation($navigation);

            return $this->renderTbody($navigation['type']);
        }

        $navigation = array(
            'id' => 0,
            'name' => '',
            'url' => '',
            'isNewWin' => 0,
            'isOpen' => 1,
            'type' => $request->query->get('type'),
            'parentId' => $request->query->get('parentId', 0),
        );
        $parentNavigation = $navigation['parentId'] ? $this->getNavigationService()->getNavigation($navigation['parentId']) : null;

        return $this->render('admin-v2/operating/navigation/navigation-modal.html.twig', array(
            'navigation' => $navigation,
            'parentNavigation' => $parentNavigation,
        ));
    }

    public function editAction(Request $request, $id)
    {
        $navigation = $this->getNavigationService()->getNavigation($id);
        $parentNavigation = $navigation['parentId'] ? $this->getNavigationService()->getNavigation($navigation['parentId']) : null;
        if ('POST' == $request->getMethod()) {
            $this->getNavigationService()->updateNavigation($id, $request->request->all());
            $navigation = $this->getNavigationService()->getNavigation($id);

            return $this->renderTbody($navigation['type']);
        }

        return $this->render('admin-v2/operating/navigation/navigation-modal.html.twig', array(
            'navigation' => $navigation,
            'parentNavigation' => $parentNavigation,
        ));
    }

    public function updateSeqsAction(Request $request)
    {
        $data = $request->request->get('data');
        $ids = ArrayToolkit::column($data, 'id');
        $this->getNavigationService()->updateNavigationsSequenceByIds($ids);

        $nav = current($data);
        $nav = $this->getNavigationService()->getNavigation($nav['id']);
        if ($nav['type'] == 'top') {
            $newcomerTask = $this->getSettingService()->get('newcomer_task', array());
            $newcomerTask = array_merge($newcomerTask, array('decoration_web_task' => array('child_task' => array('top_navigation_applied' => 1))));
            $this->getSettingService()->set('newcomer_task', $newcomerTask);
        }

        return $this->createJsonResponse(true);
    }

    protected function renderTbody($type)
    {
        $footNavigations = $this->getNavigationService()->findNavigationsByType($type, 0, 20);

        return $this->render('admin-v2/operating/navigation/tbody.html.twig', array(
            'navigations' => $footNavigations,
        ));
    }

    /**
     * @return NavigationService
     */
    protected function getNavigationService()
    {
        return $this->createService('Content:NavigationService');
    }

    /**
     * @return SettingService
     */
    protected function getSettingService()
    {
        return $this->createService('System:SettingService');
    }
}
